@extends('layouts.channel')

@section('page_type', 'sub')

@php
    $dep1_id = "02";
    $dep1_tit = "상품관리";
@endphp

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">분류관리</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>상품관리</li>
                        <li>분류관리</li>
                    </ul>
                </div>

                <div class="tab_bx1">
                    <ul>
                        <li><a href="{{ route('channel.product_own') }}"><span>자사상품관리</span></a></li>
                        <li><a href="{{ route('channel.product_public') }}"><span>공개상품관리</span></a></li>
                        <li><a href="{{ route('channel.product_partial') }}"><span>부분공개상품관리</span></a></li>
                        <li><a href="{{ route('channel.product.categories') }}" class="on"><span>분류관리</span></a></li>
                        <li><a href="{{ route('channel.product_request') }}"><span>판매 요청 관리</span></a></li>
                    </ul>
                </div>

                <div class="conbx">
                    @if(session('success_message'))
                        <p class="mt10" style="color:#087443;">{{ session('success_message') }}</p>
                    @endif
                    @if(session('error_message'))
                        <p class="fcol1 mt10">{{ session('error_message') }}</p>
                    @endif
                    @if($errors->any())
                        <p class="fcol1 mt10">{{ $errors->first() }}</p>
                    @endif

                    <div class="con_w">
                        <div class="ttl01">중분류 등록</div>
                        <form action="{{ route('channel.product.categories.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="level" value="middle">
                            <div class="tb01 textL">
                                <table>
                                    <colgroup>
                                        <col width="160px"><col width=""><col width="160px"><col width="">
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <th>대분류</th>
                                            <td>
                                                <select name="major_category_id" required>
                                                    <option value="">대분류 선택</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <th>중분류명</th>
                                            <td>
                                                <input type="text" name="category_name" class="wFull" required>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="btm_btn mt10" style="text-align:right;">
                                <button type="submit" class="btn01 col2" style="height:38px; border:0; padding:0 18px;">중분류 등록</button>
                            </div>
                        </form>
                    </div>

                    <div class="con_w">
                        <div class="ttl01">소분류 등록</div>
                        <form action="{{ route('channel.product.categories.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="level" value="minor">
                            <div class="tb01 textL">
                                <table>
                                    <colgroup>
                                        <col width="160px"><col width=""><col width="160px"><col width="">
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <th>대분류</th>
                                            <td>
                                                <select name="major_category_id" id="minor_major_category_id" required>
                                                    <option value="">대분류 선택</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <th>중분류</th>
                                            <td>
                                                <select name="middle_category_id" id="minor_middle_category_id" required>
                                                    <option value="">중분류 선택</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>소분류명</th>
                                            <td colspan="3">
                                                <input type="text" name="category_name" class="wFull" required>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="btm_btn mt10" style="text-align:right;">
                                <button type="submit" class="btn01 col2" style="height:38px; border:0; padding:0 18px;">소분류 등록</button>
                            </div>
                        </form>
                    </div>

                    <div class="con_w">
                        <div class="ttl01">등록된 분류</div>
                        <div class="tb01 textL">
                            <table>
                                <colgroup>
                                    <col width="210px"><col width="260px"><col width=""><col width="110px"><col width="170px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>대분류</th>
                                        <th>중분류</th>
                                        <th>소분류</th>
                                        <th>상태</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categories as $major)
                                        @forelse($major->subCategories as $middle)
                                            <tr>
                                                <td>{{ $major->category_name }}</td>
                                                <td colspan="2">
                                                    <form action="{{ route('channel.product.categories.update', $middle->id) }}" method="POST" style="display:flex; gap:8px; align-items:center;">
                                                        @csrf
                                                        <input type="text" name="category_name" value="{{ $middle->category_name }}" style="height:34px; border:1px solid #ddd; padding:0 8px; width:180px;">
                                                        <select name="status" class="w160">
                                                            <option value="1" {{ $middle->status == 1 ? 'selected' : '' }}>사용</option>
                                                            <option value="0" {{ $middle->status == 0 ? 'selected' : '' }}>미사용</option>
                                                        </select>
                                                        <button type="submit" style="height:34px; border:1px solid #444; background:#fff; padding:0 10px;">수정</button>
                                                    </form>
                                                </td>
                                                <td>{{ $middle->status == 1 ? '사용' : '미사용' }}</td>
                                                <td>
                                                    <form action="{{ route('channel.product.categories.delete', $middle->id) }}" method="POST" onsubmit="return confirm('분류를 삭제하시겠습니까?');">
                                                        @csrf
                                                        <button type="submit" style="height:32px; border:1px solid #ddd; background:#fff; padding:0 10px;">삭제</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @foreach($middle->subCategories as $minor)
                                                <tr>
                                                    <td>{{ $major->category_name }}</td>
                                                    <td>{{ $middle->category_name }}</td>
                                                    <td>
                                                        <form action="{{ route('channel.product.categories.update', $minor->id) }}" method="POST" style="display:flex; gap:8px; align-items:center;">
                                                            @csrf
                                                            <input type="text" name="category_name" value="{{ $minor->category_name }}" style="height:34px; border:1px solid #ddd; padding:0 8px; width:180px;">
                                                            <select name="status" class="w160">
                                                                <option value="1" {{ $minor->status == 1 ? 'selected' : '' }}>사용</option>
                                                                <option value="0" {{ $minor->status == 0 ? 'selected' : '' }}>미사용</option>
                                                            </select>
                                                            <button type="submit" style="height:34px; border:1px solid #444; background:#fff; padding:0 10px;">수정</button>
                                                        </form>
                                                    </td>
                                                    <td>{{ $minor->status == 1 ? '사용' : '미사용' }}</td>
                                                    <td>
                                                        <form action="{{ route('channel.product.categories.delete', $minor->id) }}" method="POST" onsubmit="return confirm('분류를 삭제하시겠습니까?');">
                                                            @csrf
                                                            <button type="submit" style="height:32px; border:1px solid #ddd; background:#fff; padding:0 10px;">삭제</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td>{{ $major->category_name }}</td>
                                                <td colspan="4">등록된 중분류가 없습니다.</td>
                                            </tr>
                                        @endforelse
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var categoryTree = @json($categoryTree ?? []);
    var majorSelect = document.getElementById('minor_major_category_id');
    var middleSelect = document.getElementById('minor_middle_category_id');

    function findCategory(id) {
        id = String(id || '');
        return (categoryTree || []).find(function (item) {
            return String(item.id) === id;
        }) || null;
    }

    function renderMiddleOptions() {
        if (!majorSelect || !middleSelect) {
            return;
        }

        var major = findCategory(majorSelect.value);
        middleSelect.innerHTML = '<option value="">중분류 선택</option>';
        (major ? major.children : []).forEach(function (middle) {
            var option = document.createElement('option');
            option.value = middle.id;
            option.textContent = middle.name;
            middleSelect.appendChild(option);
        });
    }

    if (majorSelect) {
        majorSelect.addEventListener('change', renderMiddleOptions);
    }
});
</script>
@endpush
