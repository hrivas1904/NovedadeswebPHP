<?php

namespace Tests\Feature;

use App\Services\Biblioteca\Content;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;
use ZipArchive;

class BibliotecaParserTest extends TestCase
{
    private function paragraph(string $text): string
    {
        return '<w:p><w:r><w:t xml:space="preserve">'.htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</w:t></w:r></w:p>';
    }

    private function table(array $rows): string
    {
        return '<w:tbl>'.implode('', array_map(fn($cells) => '<w:tr>'.implode('', array_map(
            fn($paragraphs) => '<w:tc>'.implode('', array_map($this->paragraph(...), (array)$paragraphs)).'</w:tc>',
            $cells
        )).'</w:tr>', $rows)).'</w:tbl>';
    }

    private function parse(string $body): array
    {
        $path=sys_get_temp_dir().DIRECTORY_SEPARATOR.'biblioteca-parser-'.Str::uuid().'.docx';
        $zip=new ZipArchive;
        $this->assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$body.'</w:body></w:document>');
        $zip->close();
        try {
            $process=new Process([getenv('BIBLIOTECA_TEST_PYTHON') ?: 'python', resource_path('biblioteca/parse_document.py'), $path]);
            $process->mustRun();
            return json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
        } finally {
            if(is_file($path))unlink($path);
        }
    }

    private function remainingSections(): string
    {
        $paragraphs=[
            '2. Propósito del Puesto', 'Preservar el propósito completo.',
            '3. Principales Tareas y Responsabilidades', 'Realizar la tarea.',
            'Alcance de las responsabilidades y colaboración', 'Cláusula adicional íntegra.',
            '4. Contexto del Puesto', 'Relaciones internas: Administración.', 'Relaciones externas: Proveedores.',
            '5. Perfil del Puesto', 'Formación: Estudios completos.', 'Experiencia: Un año.', 'Conocimientos: Sistemas.',
            '6. Competencias Requeridas', 'Genéricas: Organización. Confidencialidad.', 'Específicas:', 'Control documental.',
            '7. Compromiso', 'Respetar la confidencialidad.',
            '8. Contribución al Hospital', 'Acompañar la misión.'
        ];
        return implode('', array_map($this->paragraph(...), $paragraphs)).
            $this->table([[['Empleado/a', 'Firma y aclaración – Fecha: ___/___/______'], ['Recursos Humanos', 'Firma y aclaración – Fecha: ___/___/______']]]);
    }

    public function test_new_identification_keeps_category_agreement_and_unknown_rows_without_duplicate_main_fields(): void
    {
        $p=$this->parse($this->paragraph('Descriptivo de Puesto – Personal de Esterilización').
            $this->paragraph('1. Identificación del Puesto').
            $this->table([
                ['Nombre del puesto / rol: Personal de Esterilización'],
                ['Categoría actual: A) a: Obstétricas e instrumentadoras'],
                ['Convenio: CCT 122/75 – Sanidad', 'Área: Esterilización'],
                ['Servicio / rol de referencia: ESTERILIZACIÓN'],
                ['Reporta a: Por confirmar', 'Supervisor directo: Jefatura'],
                ['Personal a cargo: Por confirmar'],
                ['Nivel de riesgo: Alto'],
            ]).$this->remainingSections());

        $this->assertSame('standard', $p['structure']);
        $this->assertSame('Personal de Esterilización', $p['fields']['name']);
        $this->assertSame('A) a: Obstétricas e instrumentadoras', $p['metadata']['category']);
        $this->assertSame('CCT 122/75 – Sanidad', $p['metadata']['agreement']);
        $this->assertSame('ESTERILIZACIÓN', $p['metadata']['sector']);
        $this->assertSame('Por confirmar', $p['fields']['supervises']);
        $c=Content::validateJob(Content::fromParsed($p));
        foreach(array_keys(config('biblioteca.groups')) as $key)$this->assertNotEmpty($c['groups'][$key], $key);
        $this->assertSame('Organización. Confidencialidad.', $c['groups']['generic'][0]['text']);
        $this->assertSame('bullet', $c['groups']['commitment'][0]['list']['format']);
        $extra=Content::json($c['additional']);
        foreach(['Categoría actual: A) a: Obstétricas e instrumentadoras','Convenio: CCT 122/75 – Sanidad','Nivel de riesgo: Alto','Firma y aclaración'] as $text)$this->assertStringContainsString($text,$extra);
        foreach(['Nombre del puesto / rol','Personal de Esterilización','Área: Esterilización','Servicio / rol de referencia','Reporta a:','Supervisor directo:','Personal a cargo:'] as $text)$this->assertStringNotContainsString($text,$extra);
        $this->assertSame('Cláusula adicional íntegra.', $c['groups']['tasks'][2]['text']);
        $this->assertSame('Espacios de firma', $c['additional'][1]['title']);
    }

    public function test_numbered_legacy_labels_and_values_in_separate_paragraphs_remain_supported(): void
    {
        $p=$this->parse($this->paragraph('Descriptivo de Puesto – Auxiliar').
            $this->table([
                [['1. Nombre del puesto', 'Auxiliar']],
                [['1.b Área', 'Administración']],
                [['1.c Reporta a', 'Jefatura']],
                [['1.d Supervisor directo', 'Responsable']],
                [['1.e Supervisa el trabajo de', '-']],
            ]).$this->remainingSections());

        $this->assertSame(['name'=>'Auxiliar','area'=>'Administración','reportsTo'=>'Jefatura','supervisor'=>'Responsable','supervises'=>'-'],$p['fields']);
        $this->assertCount(2, $p['fieldValueLocations']['name']);
        $c=Content::validateJob(Content::fromParsed($p));
        $this->assertSame('Auxiliar', $c['name']);
        $this->assertSame('signatures', $c['additional'][0]['key']);
        $this->assertCount(1, $c['additional']);
    }

    public function test_empty_identification_does_not_consume_the_following_field(): void
    {
        $p=$this->parse($this->paragraph('Descriptivo de Puesto – Auxiliar').
            $this->table([['Nombre del puesto / rol: Auxiliar'],['Área: Administración'],['Reporta a:','Supervisor directo: Jefatura']]).
            $this->remainingSections());
        $this->assertNull($p['fields']['reportsTo']);
        $this->assertSame('Jefatura', $p['fields']['supervisor']);
    }

    public function test_explicit_sector_keeps_distinct_reference_service_in_additional_information(): void
    {
        $p=$this->parse($this->paragraph('Descriptivo de Puesto – Auxiliar').
            $this->table([['Nombre del puesto / rol: Auxiliar'],['Área: Administración'],['Sector: Contabilidad'],['Servicio / rol de referencia: ADM']]).
            $this->remainingSections());
        $c=Content::validateJob(Content::fromParsed($p));
        $this->assertSame('Contabilidad',$c['sector']);
        $this->assertStringContainsString('Servicio / rol de referencia: ADM',Content::json($c['additional']));
    }


    public function test_competency_sentences_starting_with_other_field_names_are_kept_in_full(): void
    {
        $sections=str_replace('Control documental.', 'Conocimientos sólidos en liquidación de sueldos y cargas sociales.', $this->remainingSections());
        $sections=str_replace($this->paragraph('7. Compromiso'), $this->paragraph('Conocimientos: dominio técnico específico.').$this->paragraph('7. Compromiso'), $sections);
        $p=$this->parse($this->paragraph('Descriptivo de Puesto – Analista').
            $this->table([['Nombre del puesto / rol: Analista'],['Área: Recursos Humanos']]).$sections);
        $c=Content::validateJob(Content::fromParsed($p));
        $this->assertSame([
            'Conocimientos sólidos en liquidación de sueldos y cargas sociales.',
            'Conocimientos: dominio técnico específico.',
        ],array_column($c['groups']['specific'],'text'));
        $this->assertSame(['Sistemas.'],array_column($c['groups']['knowledge'],'text'));
    }
}
