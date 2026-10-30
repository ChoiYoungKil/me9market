@foreach($document['blocks'] as $block)
    @if($block['type'] === 'table')<div class="policy-table-scroll"><table><tbody>@foreach($block['rows'] as $row)<tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>@endforeach</tbody></table></div>
    @else<p>{{ $block['text'] }}</p>@endif
@endforeach
