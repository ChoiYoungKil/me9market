{{-- NOTE: THIS WHOLE PAGE IS INCLUDED IN add_edit_category.blade.php!! ( <div id="appendCategoriesLevel"> ) --}}
    {{-- Show Categories <select>
        <option> depending on the chosen selected Section (show the relevant categories of the chosen section) in
            append_categories_level.blade.php page using AJAX --}}
            {{-- We created this <div> in a separate file in order for the appendCategoryLevel() method inside the
                CategoryController to be able to return the whole file as a response to the AJAX call to show the
                proper/relevant categories <select> box <option> depending on the chosen selected Section --}}

                        <div class="f_w mt40">
                            <div class="f_ttl">상위 분류</div>
                            <div class="tb01">
                                <table class="two">
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>상위 분류 선택</span></th>
                                            <td colspan="3">
                                                <select name="parent_id" id="parent_id" class="w300">
                                                    <option value="0" @if (isset($category['parent_id']) && $category['parent_id'] == 0) selected @endif>최상위 분류</option>
                                                    @if (!empty($getCategories))
                                                        {{-- Show the Categories --}}
                                                        @foreach ($getCategories as $parentCategory) {{-- Level 1 --}}
                                                            <option value="{{ $parentCategory['id'] }}" @if (isset($category['parent_id']) && $category['parent_id'] == $parentCategory['id']) selected @endif>
                                                                {{ $parentCategory['category_name'] }}</option>

                                                            {{-- Show the Subcategories (Level 2) --}}
                                                            @if (!empty($parentCategory['subCategories']))
                                                                @foreach ($parentCategory['subCategories'] as $subcategory)
                                                                    <option value="{{ $subcategory['id'] }}" @if (isset($category['parent_id']) && $category['parent_id'] == $subcategory['id']) selected @endif>
                                                                        &nbsp;&raquo;&nbsp;{{ $subcategory['category_name'] }}</option>

                                                                    {{-- Show the Sub-Subcategories (Level 3) --}}
                                                                    @if (!empty($subcategory['subCategories']))
                                                                        @foreach ($subcategory['subCategories'] as $subsubcategory)
                                                                            <option value="{{ $subsubcategory['id'] }}" @if (isset($category['parent_id']) && $category['parent_id'] == $subsubcategory['id']) selected @endif>
                                                                                &nbsp;&nbsp;&nbsp;&raquo;&raquo;&nbsp;{{ $subsubcategory['category_name'] }}
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <p style="margin-top: 5px; font-size: 12px; color: #666;">※ 최대 3단계까지 분류를
                                                    생성할 수 있습니다.</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>