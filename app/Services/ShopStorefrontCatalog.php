<?php

namespace App\Services;

use App\Models\Category;
use App\Models\JointPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ShopStorefrontCatalog
{
    public function activeJointPurchases(): Collection
    {
        return JointPurchase::where('status', 1)
            ->where(fn ($q) => $q->whereNull('start_date')->orWhereDate('start_date', '<=', now('Asia/Seoul')->toDateString()))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', now('Asia/Seoul')->toDateString()))
            ->orderByDesc('id')->get()->unique('product_id')->keyBy('product_id');
    }

    public function matches($listing, string $keyword): bool
    {
        return mb_stripos(implode(' ', [$listing->product->product_name, $listing->product->product_code, $listing->product->meta_keywords, $listing->product->group_code]), $keyword) !== false;
    }

    public function listing(Request $request, bool $joint = false): array
    {
        $data = $request->validate(['search' => 'nullable|string|max:100', 'sort' => 'nullable|in:latest,deadline,price_asc,price_desc,rating',
            'category' => 'nullable|integer|min:1', 'category1' => 'nullable|integer|min:1', 'category2' => 'nullable|integer|min:1', 'category3' => 'nullable|integer|min:1', 'page' => 'nullable|integer|min:1|max:100000']);
        $all = app(ShopChannelRuntime::class)->products();
        $jointByProduct = $this->activeJointPurchases();
        $hasJointPurchases = $all->contains(fn ($p) => $jointByProduct->has($p->product_id));
        $products = $all->filter(fn ($p) => $joint === $jointByProduct->has($p->product_id));
        $tree = Category::where('status', 1)->get(['id', 'parent_id', 'category_name'])->keyBy('id');
        $allowed = [];
        foreach ($products as $item) {
            $id = (int) $item->product->category_id;
            $seen = [];
            while ($id && $tree->has($id) && ! isset($seen[$id])) {
                $seen[$id] = $allowed[$id] = true;
                $id = (int) $tree[$id]->parent_id;
            }
        }
        $categoryTree = $tree->filter(fn ($c) => isset($allowed[$c->id]))->values();
        foreach ([1, 2, 3] as $level) {
            $id = $data['category'.$level] ?? null;
            if ($id && (! $tree->has((int) $id) || (int) $tree[(int) $id]->parent_id !== (int) ($level === 1 ? 0 : ($data['category'.($level - 1)] ?? 0)))) {
                throw ValidationException::withMessages(['category'.$level => '상위 분류에 해당하는 상품 분류를 선택해 주세요.']);
            }
        }
        $selected = (int) ($data['category3'] ?? $data['category2'] ?? $data['category1'] ?? $data['category'] ?? 0);
        if ($selected) {
            $products = $products->filter(function ($p) use ($selected, $tree) {
                $id = (int) $p->product->category_id;
                $seen = [];
                while ($id && ! isset($seen[$id])) {
                    if ($id === $selected) return true;
                    $seen[$id] = true;
                    $id = (int) ($tree[$id]->parent_id ?? 0);
                }
                return false;
            });
        }
        if (filled($data['search'] ?? null)) {
            $term = trim($data['search']);
            $products = $products->filter(fn ($p) => mb_stripos($p->product->product_name.' '.$p->product->product_code, $term) !== false);
        }
        $price = fn ($p) => $joint ? app(JointPurchasePricingService::class)->priceForQuantity($jointByProduct[$p->product_id], max(1, (int) $jointByProduct[$p->product_id]->current_quantity))['unit_price'] : ($p->selling_price ?: $p->product_price);
        $products = match ($data['sort'] ?? ($joint ? 'deadline' : 'latest')) {
            'price_asc' => $products->sortBy($price), 'price_desc' => $products->sortByDesc($price),
            'rating' => $products->sortByDesc(fn ($p) => (float) $p->product->approved_ratings_avg_rating),
            'deadline' => $products->sortBy(fn ($p) => $jointByProduct[$p->product_id]->end_date ?? '9999-12-31'),
            default => $products->sortByDesc('id'),
        };
        $page = (int) ($data['page'] ?? 1);
        $products = new LengthAwarePaginator($products->values()->forPage($page, 12), $products->count(), 12, $page, ['path' => $request->url(), 'query' => $request->query()]);
        return compact('products', 'categoryTree', 'hasJointPurchases', 'jointByProduct', 'joint');
    }
}
