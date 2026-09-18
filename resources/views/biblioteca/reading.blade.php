@if($entry['job'])
<section class="bib-section"><h3>Identificación del puesto</h3><dl class="bib-metadata">@foreach(['Área'=>$entry['area'],'Sector / servicio'=>$entry['content']['sector']??'','Reporta a'=>$entry['content']['reportsTo']??'','Supervisor directo'=>$entry['content']['supervisor']??'','Supervisa / personal a cargo'=>$entry['content']['supervises']??''] as $label=>$value)<div><dt>{{ $label }}</dt><dd>{{ $value?:'Sin registrar' }}</dd></div>@endforeach</dl></section>
@foreach($entry['parsed']['sections']??[] as $section)
@if($section['key']!=='identification' && !empty($section['blocks']))<section class="bib-section" id="seccion-{{ $loop->index }}"><h3>{{ $section['title'] }}</h3><div class="bib-reading">@include('biblioteca.blocks',['blocks'=>$section['blocks'],'bullets'=>in_array($section['key'],['competencies','generic','specific','commitment'])])</div></section>@endif
@endforeach
@if(!empty($entry['parsed']['extras']))<section class="bib-section"><h3>Contenido adicional de la fuente</h3><div class="bib-reading">@include('biblioteca.blocks',['blocks'=>$entry['parsed']['extras'],'bullets'=>false])</div></section>@endif
@else
<section class="bib-section"><h3>Control del documento</h3><dl class="bib-metadata">@foreach(['Código'=>$entry['content']['code'],'Versión documental'=>$entry['content']['version'],'Responsable'=>$entry['content']['responsible'],'Vigencia desde'=>$entry['content']['validFrom'],'Aprobación'=>$entry['content']['approver'],'Instrumento de aprobación'=>$entry['content']['approvalRecord'],'Última revisión'=>$entry['content']['lastReview'],'Próxima revisión'=>$entry['nextReview']] as $label=>$value)<div><dt>{{ $label }}</dt><dd>{{ $value?:'Sin registrar' }}</dd></div>@endforeach</dl></section>
@if($entry['content']['summary'])<p class="bib-summary">{{ $entry['content']['summary'] }}</p>@endif
@foreach($entry['content']['sections'] as $section)<section class="bib-section" id="seccion-{{ $loop->index }}"><h3>{{ $section['title'] }}</h3><div class="bib-reading">{!! \App\Services\Biblioteca\Content::sanitize($section['html']) !!}</div></section>@endforeach
@endif
