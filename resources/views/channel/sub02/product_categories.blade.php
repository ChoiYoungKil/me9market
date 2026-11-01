@extends('layouts.channel')
@section('page_type', 'sub')
@php
    $dep1_id = '02';
    $dep1_tit = '상품관리';
    $categoryVendorId = (int) auth('admin')->user()->vendor_id;
    $usesCategoryOwnership = \Illuminate\Support\Facades\Schema::hasColumn('categories', 'vendor_id');
@endphp
@push('styles')
<link rel="stylesheet" href="{{ asset('channel_assets/css/catalog-settings.css') }}?v={{ filemtime(public_path('channel_assets/css/catalog-settings.css')) }}">
@endpush
@section('content')
<div id="contents" class="channel-category-manager">
    <div class="row"><div class="box box1">
        <div class="page_info"><h1 class="ttl">분류관리</h1><ul class="dep"><li>HOME</li><li>상품관리</li><li>분류관리</li></ul></div>
        <div class="tab_bx1"><ul>
            <li><a href="{{ route('channel.product_own') }}"><span>자사상품관리</span></a></li>
            <li><a href="{{ route('channel.product_public') }}"><span>공개상품관리</span></a></li>
            <li><a href="{{ route('channel.product_partial') }}"><span>부분공개상품관리</span></a></li>
            <li><a href="{{ route('channel.product.categories') }}" class="on" aria-current="page"><span>분류관리</span></a></li>
            <li><a href="{{ route('channel.product_request') }}"><span>판매 요청 관리</span></a></li>
        </ul></div>
        @if(session('success_message'))<p class="category-feedback" role="status">{{ session('success_message') }}</p>@endif
        @if(session('error_message'))<p class="category-feedback error" role="alert">{{ session('error_message') }}</p>@endif
        @if($errors->any())<div class="category-feedback error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    </div></div>
    <div class="row category-registration-grid">
        <section class="box box1">
            <h2 class="ttl01">중분류 등록</h2>
            <form action="{{ route('channel.product.categories.store') }}" method="POST">
                @csrf<input type="hidden" name="level" value="middle">
                <div class="tb01 category-create-table"><table><colgroup><col class="category-label-col"><col></colgroup><tbody>
                    <tr><th><label for="middle-major-category">대분류</label></th><td><select name="major_category_id" id="middle-major-category" required><option value="">대분류 선택</option>
                        @foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('level') === 'middle' && (string) old('major_category_id') === (string) $category->id)>{{ $category->category_name }}</option>@endforeach
                    </select></td></tr>
                    <tr><th><label for="middle-category-name">중분류명</label></th><td><input type="text" name="category_name" id="middle-category-name" value="{{ old('level') === 'middle' ? old('category_name') : '' }}" placeholder="중분류명을 입력해 주세요" maxlength="100" required></td></tr>
                </tbody></table></div>
                <div class="category-form-actions"><button type="reset" class="btn02 col3">입력 초기화</button><button type="submit" class="btn02 col5">중분류 등록</button></div>
            </form>
        </section>
        <section class="box box1">
            <h2 class="ttl01">소분류 등록</h2>
            <form action="{{ route('channel.product.categories.store') }}" method="POST" id="minor-category-form">
                @csrf<input type="hidden" name="level" value="minor">
                <div class="tb01 category-create-table"><table><colgroup><col class="category-label-col"><col></colgroup><tbody>
                    <tr><th><label for="minor_major_category_id">대분류</label></th><td><select name="major_category_id" id="minor_major_category_id" required><option value="">대분류 선택</option>
                        @foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('level') === 'minor' && (string) old('major_category_id') === (string) $category->id)>{{ $category->category_name }}</option>@endforeach
                    </select></td></tr>
                    <tr><th><label for="minor_middle_category_id">중분류</label></th><td><select name="middle_category_id" id="minor_middle_category_id" required data-initial-value="{{ old('level') === 'minor' ? old('middle_category_id') : '' }}"><option value="">대분류를 먼저 선택해 주세요</option></select></td></tr>
                    <tr><th><label for="minor-category-name">소분류명</label></th><td><input type="text" name="category_name" id="minor-category-name" value="{{ old('level') === 'minor' ? old('category_name') : '' }}" placeholder="소분류명을 입력해 주세요" maxlength="100" required></td></tr>
                </tbody></table></div>
                <div class="category-form-actions"><button type="reset" class="btn02 col3">입력 초기화</button><button type="submit" class="btn02 col5">소분류 등록</button></div>
            </form>
        </section>
    </div>
    <div class="row"><section class="box box2">
        <h2 class="ttl01">등록된 분류</h2>
        <p class="category-help">직접 등록한 분류는 이름과 상태를 변경한 뒤 수정 버튼으로 저장합니다. 공통 분류는 전체관리자에서 관리합니다.</p>
        <div class="tb01 ovS category-list-table"><table><colgroup><col style="width:20%"><col><col><col style="width:110px"><col style="width:160px"></colgroup>
            <thead><tr><th>대분류</th><th>중분류</th><th>소분류</th><th>상태</th><th>관리</th></tr></thead>
            <tbody>
                @forelse($categories as $major)
                    @forelse($major->subCategories as $middle)
                        @include('channel.sub02.partials.category_row', ['node' => $middle, 'parentMiddle' => null])
                        @foreach($middle->subCategories as $minor)
                            @include('channel.sub02.partials.category_row', ['node' => $minor, 'parentMiddle' => $middle])
                        @endforeach
                    @empty<tr><td class="category-parent">{{ $major->category_name }}</td><td colspan="4" class="category-empty">등록된 중분류가 없습니다.</td></tr>@endforelse
                @empty
                    @include('components.table-empty-row', ['columns' => 5])
                @endforelse
            </tbody>
        </table></div>
    </section></div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var categoryTree = @json($categoryTree ?? []);
    var majorSelect = document.getElementById('minor_major_category_id');
    var middleSelect = document.getElementById('minor_middle_category_id');
    function renderMiddleOptions(selected) {
        var major = categoryTree.find(function (item) { return String(item.id) === majorSelect.value; });
        middleSelect.replaceChildren();
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = major ? '중분류 선택' : '대분류를 먼저 선택해 주세요';
        middleSelect.appendChild(placeholder);
        (major ? major.children : []).forEach(function (middle) {
            var option = document.createElement('option');
            option.value = middle.id;
            option.textContent = middle.name;
            option.selected = String(middle.id) === String(selected || '');
            middleSelect.appendChild(option);
        });
    }
    majorSelect.addEventListener('change', function () { renderMiddleOptions(); });
    document.getElementById('minor-category-form').addEventListener('reset', function () {
        setTimeout(function () { renderMiddleOptions(middleSelect.dataset.initialValue); }, 0);
    });
    renderMiddleOptions(middleSelect.dataset.initialValue);
});
</script>
@endpush
