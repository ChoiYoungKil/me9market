<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $categories = [
        '식품/건강기능식품' => 'notice-food-health',
        '의약외품/화장품' => 'notice-cosmetics',
        '의류/패션' => 'notice-fashion',
        '전자제품/전기용품' => 'notice-electronics',
        '어린이제품/완구/유아용품' => 'notice-children',
        '가구/인테리어' => 'notice-furniture',
        '주류/담배' => 'notice-liquor-tobacco',
        '자동차/오토바이 부품' => 'notice-vehicle-parts',
        '기타 수입품' => 'notice-imported',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('sections') || !Schema::hasTable('categories')) {
            return;
        }

        $sectionId = DB::table('sections')->where('status', 1)->orderBy('id')->value('id');
        if (!$sectionId) {
            $sectionId = DB::table('sections')->insertGetId([
                'name' => '상품분류',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($this->categories as $name => $url) {
            $existing = DB::table('categories')
                ->where('parent_id', 0)
                ->where(function ($query) use ($name, $url) {
                    $query->where('category_name', $name)->orWhere('url', $url);
                })
                ->first();

            if ($existing) {
                DB::table('categories')->where('id', $existing->id)->update([
                    'category_name' => $name,
                    'section_id' => $existing->section_id ?: $sectionId,
                    'url' => $existing->url ?: $url,
                    'status' => 1,
                    'updated_at' => now(),
                ]);
                continue;
            }

            DB::table('categories')->insert([
                'parent_id' => 0,
                'section_id' => $sectionId,
                'category_name' => $name,
                'category_image' => '',
                'category_discount' => 0,
                'description' => '',
                'url' => $this->uniqueCategoryUrl($url),
                'meta_title' => $name,
                'meta_description' => $name,
                'meta_keywords' => $name,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('categories') || !Schema::hasTable('products')) {
            return;
        }

        foreach ($this->categories as $url) {
            $category = DB::table('categories')
                ->where('parent_id', 0)
                ->where('url', $url)
                ->first();

            if (!$category) {
                continue;
            }

            $hasProducts = DB::table('products')->where('category_id', $category->id)->exists();
            $hasChildren = DB::table('categories')->where('parent_id', $category->id)->exists();
            if (!$hasProducts && !$hasChildren) {
                DB::table('categories')->where('id', $category->id)->delete();
            }
        }
    }

    private function uniqueCategoryUrl(string $baseUrl): string
    {
        $url = Str::slug($baseUrl) ?: $baseUrl;
        $candidate = $url;
        $suffix = 2;

        while (DB::table('categories')->where('url', $candidate)->exists()) {
            $candidate = $url . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }
};
