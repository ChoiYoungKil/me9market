<option value="{{ $category['id'] }}" @selected((string) $selectedCategory === (string) $category['id'])>{{ str_repeat('　', $depth) }}{{ $depth ? '└ ' : '' }}{{ $category['category_name'] }}</option>
@foreach ($category['sub_categories'] ?? [] as $childCategory)
    @include('admin.products.partials.category_option', ['category' => $childCategory, 'depth' => $depth + 1, 'selectedCategory' => $selectedCategory])
@endforeach
