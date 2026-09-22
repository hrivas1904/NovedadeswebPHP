<?php

namespace App\Services\Biblioteca;

use DOMDocument;
use ZipArchive;

class WordExport
{
    private function text(string $text): string {return htmlspecialchars($text,ENT_XML1|ENT_QUOTES,'UTF-8');}
    private function paragraph(string $text,string $style='',bool $bullet=false): string {
        $properties=($style?'<w:pStyle w:val="'.$style.'"/>':'').($bullet?'<w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr>':'');
        return '<w:p><w:pPr>'.$properties.'<w:spacing w:after="120"/></w:pPr><w:r><w:t xml:space="preserve">'.$this->text(trim($text)).'</w:t></w:r></w:p>';
    }
    private function nodes($node): string {
        $xml='';foreach($node->childNodes as $child) {
            if(!$child instanceof \DOMElement)continue;$tag=strtolower($child->tagName);
            if($tag==='table') {
                $xml.='<w:tbl><w:tblPr><w:tblW w:w="5000" w:type="pct"/><w:tblBorders><w:top w:val="single" w:sz="4"/><w:left w:val="single" w:sz="4"/><w:bottom w:val="single" w:sz="4"/><w:right w:val="single" w:sz="4"/><w:insideH w:val="single" w:sz="4"/><w:insideV w:val="single" w:sz="4"/></w:tblBorders></w:tblPr>';
                foreach($child->getElementsByTagName('tr') as $row) {
                    $parent=$row->parentNode;while($parent&&strtolower($parent->nodeName)!=='table')$parent=$parent->parentNode;if($parent!==$child)continue;
                    $xml.='<w:tr>';foreach($row->childNodes as $cell)if($cell instanceof \DOMElement&&in_array(strtolower($cell->tagName),['th','td'])) {$span=max(1,(int)$cell->getAttribute('colspan'));$xml.='<w:tc><w:tcPr>'.($span>1?'<w:gridSpan w:val="'.$span.'"/>':'').'</w:tcPr>'.$this->paragraph($cell->textContent).'</w:tc>';}$xml.='</w:tr>';
                }$xml.='</w:tbl>';
            } elseif(in_array($tag,['p','h1','h2','h3','h4','li','dt','dd']))$xml.=$this->paragraph($child->textContent,str_starts_with($tag,'h')?'Heading'.min((int)substr($tag,1),3):'',$tag==='li');
            else $xml.=$this->nodes($child);
        }return $xml;
    }
    public function create(string $html): string {
        $dom=new DOMDocument();$old=libxml_use_internal_errors(true);$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET);libxml_clear_errors();libxml_use_internal_errors($old);
        $body=$this->nodes($dom->getElementsByTagName('body')->item(0));
        $folder=storage_path('app/private/biblioteca/exports');if(!is_dir($folder))mkdir($folder,0770,true);$file=tempnam($folder,'word-');
        $zip=new ZipArchive();if($zip->open($file,ZipArchive::OVERWRITE)!==true)throw new \RuntimeException('No se pudo generar Word.');
        $zip->addFromString('[Content_Types].xml','<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/><Override PartName="/word/numbering.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml"/></Types>');
        $zip->addFromString('_rels/.rels','<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
        $zip->addFromString('word/_rels/document.xml.rels','<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/></Relationships>');
        $zip->addFromString('word/document.xml','<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$body.'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134"/></w:sectPr></w:body></w:document>');
        $zip->addFromString('word/styles.xml','<?xml version="1.0"?><w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:sz w:val="22"/></w:rPr></w:rPrDefault></w:docDefaults><w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:pPr><w:keepNext/></w:pPr><w:rPr><w:b/><w:sz w:val="36"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:pPr><w:keepNext/></w:pPr><w:rPr><w:b/><w:sz w:val="30"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Heading3"><w:name w:val="heading 3"/><w:pPr><w:keepNext/></w:pPr><w:rPr><w:b/><w:sz w:val="25"/></w:rPr></w:style></w:styles>');
        $zip->addFromString('word/numbering.xml','<?xml version="1.0" encoding="UTF-8"?><w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:abstractNum w:abstractNumId="0"><w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="•"/><w:pPr><w:ind w:left="420" w:hanging="210"/></w:pPr></w:lvl></w:abstractNum><w:num w:numId="1"><w:abstractNumId w:val="0"/></w:num></w:numbering>');
        $zip->close();return $file;
    }
}
