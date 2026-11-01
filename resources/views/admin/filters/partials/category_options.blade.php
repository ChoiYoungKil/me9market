<option value="{{ $category['id'] }}" @selected(in_array((string) $category['id'], $selectedCategories, true))>{{ str_repeat('　', $depth) }}{{ $depth ? '└ ' : '' }}{{ $category['category_name'] }}</option>
@foreach($category['sub_categories'] ?? [] as $child)
    @include('admin.filters.partials.category_options', ['category' => $child, 'depth' => $depth + 1])
@endforeach
