@php
    $editable = !$usesCategoryOwnership || (int) $node->vendor_id === $categoryVendorId;
    $editForm = 'category-edit-'.$node->id;
    $restore = (string) old('edit_category_id') === (string) $node->id;
@endphp
<tr>
    <td class="category-parent">{{ $major->category_name }}</td>
    <td>
        @if($parentMiddle){{ $parentMiddle->category_name }}
        @elseif($editable)<input form="{{ $editForm }}" type="text" name="category_name" value="{{ $restore ? old('category_name') : $node->category_name }}" maxlength="100" required aria-label="{{ $node->category_name }} 분류명">
        @else{{ $node->category_name }}@endif
    </td>
    <td>
        @if(!$parentMiddle)<span class="category-empty">—</span>
        @elseif($editable)<input form="{{ $editForm }}" type="text" name="category_name" value="{{ $restore ? old('category_name') : $node->category_name }}" maxlength="100" required aria-label="{{ $node->category_name }} 분류명">
        @else{{ $node->category_name }}@endif
    </td>
    <td>@if($editable)
        <select form="{{ $editForm }}" name="status" aria-label="{{ $node->category_name }} 상태">
            <option value="1" @selected((int)($restore ? old('status') : $node->status) === 1)>사용</option>
            <option value="0" @selected((int)($restore ? old('status') : $node->status) === 0)>미사용</option>
        </select>
    @else<span class="category-state">{{ $node->status == 1 ? '사용' : '미사용' }}</span>@endif</td>
    <td>@if($editable)<div class="category-row-actions">
        <form id="{{ $editForm }}" action="{{ route('channel.product.categories.update', $node->id) }}" method="POST">@csrf<input type="hidden" name="edit_category_id" value="{{ $node->id }}"><button type="submit" class="btn02 col5">수정</button></form>
        <form action="{{ route('channel.product.categories.delete', $node->id) }}" method="POST" onsubmit="return confirm('분류를 삭제하시겠습니까?');">@csrf<button type="submit" class="btn02 col3 category-delete">삭제</button></form>
    </div>@else<span class="category-common" title="공통 분류는 전체관리자에서 관리합니다.">공통 분류</span>@endif</td>
</tr>
