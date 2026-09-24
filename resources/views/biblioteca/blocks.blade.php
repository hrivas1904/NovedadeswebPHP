@foreach($blocks as $block)
@if($block['kind']==='table')
<div class="bib-table-wrap"><table class="bib-content-table"><tbody>@foreach($block['rows']??[] as $row)<tr>@foreach($row['cells'] as $cell)<td colspan="{{ (int)$cell['colspan'] }}">@if(!empty($cell['blocks']))@include('biblioteca.blocks',['blocks'=>$cell['blocks'],'bullets'=>false])@else{{ $cell['text'] }}@endif</td>@endforeach</tr>@endforeach</tbody></table></div>
@elseif(isset($block['html']) && trim($block['text'])!=='')
@if(!empty($block['heading']))<div class="bib-rich-block bib-rich-block-heading" role="heading" aria-level="4">{!! \App\Services\Biblioteca\Content::sanitize($block['html']) !!}</div>
@elseif(($block['list']['format']??'')==='decimal')<ol class="bib-block-list" start="{{ max(1,(int)($block['list']['start']??1)) }}"><li><div class="bib-rich-block">{!! \App\Services\Biblioteca\Content::sanitize($block['html']) !!}</div></li></ol>
@elseif(!empty($bullets)||!empty($block['list']))<ul class="bib-block-list"><li><div class="bib-rich-block">{!! \App\Services\Biblioteca\Content::sanitize($block['html']) !!}</div></li></ul>
@else<div class="bib-rich-block">{!! \App\Services\Biblioteca\Content::sanitize($block['html']) !!}</div>
@endif
@elseif(!empty($block['heading']))<h4>{{ $block['text'] }}</h4>
@elseif(($block['list']['format']??'')==='decimal'&&trim($block['text'])!=='')<ol class="bib-block-list" start="{{ max(1,(int)($block['list']['start']??1)) }}"><li>{!! nl2br(e(preg_replace('/^(?:[-•]|\d+[.)])\s+/u','',$block['text']))) !!}</li></ol>
@elseif((!empty($bullets)||!empty($block['list']))&&trim($block['text'])!=='')<ul class="bib-block-list"><li>{!! nl2br(e(preg_replace('/^(?:[-•]|\d+[.)])\s+/u','',$block['text']))) !!}</li></ul>
@elseif(trim($block['text'])!=='')<p>{!! nl2br(e($block['text'])) !!}</p>
@endif
@endforeach
