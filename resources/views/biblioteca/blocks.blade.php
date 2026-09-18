@foreach($blocks as $block)
@if($block['kind']==='table')
<div class="bib-table-wrap"><table class="bib-content-table"><tbody>@foreach($block['rows']??[] as $row)<tr>@foreach($row['cells'] as $cell)<td colspan="{{ (int)$cell['colspan'] }}">@if(!empty($cell['blocks']))@include('biblioteca.blocks',['blocks'=>$cell['blocks'],'bullets'=>false])@else{{ $cell['text'] }}@endif</td>@endforeach</tr>@endforeach</tbody></table></div>
@elseif(!empty($block['heading']))<h4>{{ $block['text'] }}</h4>
@elseif((!empty($bullets)||!empty($block['list']))&&trim($block['text'])!=='')<ul class="bib-block-list"><li>{!! nl2br(e(preg_replace('/^(?:[-•]|\d+[.)])\s+/u','',$block['text']))) !!}</li></ul>
@elseif(trim($block['text'])!=='')<p>{!! nl2br(e($block['text'])) !!}</p>
@endif
@endforeach
