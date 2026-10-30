<?php

namespace App\Http\Controllers\Front;

use App\Models\Brand;
use App\Models\JointPurchase;
use App\Models\OrdersProduct;
use App\Models\PlatformRefundPolicy;
use App\Models\Product;
use App\Services\JointPurchasePricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ChannelJointPurchaseController extends ChannelProductController
{
    private function owned(int $id): JointPurchase
    {
        return JointPurchase::whereHas('product', fn ($q) => $q->where('vendor_id', Auth::guard('admin')->user()->vendor_id))->findOrFail($id);
    }

    public function index(Request $request)
    {
        $filters = $request->validate(['q' => 'nullable|string|max:150', 'search_type' => 'nullable|in:name,code', 'date_from' => 'nullable|date_format:Y-m-d', 'date_to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('date_from') ? ['after_or_equal:date_from'] : [])], 'states' => 'nullable|array', 'states.*' => 'in:0,1,2', 'per_page' => 'nullable|in:20,40,60,80,100']);
        $query = JointPurchase::with(['product.images'])->whereHas('product', fn ($q) => $q->where('vendor_id', Auth::guard('admin')->user()->vendor_id));
        if ($q = $filters['q'] ?? '') {
            $query->where(function ($w) use ($q, $filters) {
                if (($filters['search_type'] ?? 'name') === 'code') {
                    $w->where('purchase_code', 'like', '%'.$q.'%')->orWhereHas('product', fn ($p) => $p->where('product_code', 'like', '%'.$q.'%'));
                } else {
                    $w->where('sale_name', 'like', '%'.$q.'%')->orWhereHas('product', fn ($p) => $p->where('product_name', 'like', '%'.$q.'%'));
                }
            });
        }
        if ($filters['date_from'] ?? '') {
            $query->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $filters['date_from']));
        }
        if ($filters['date_to'] ?? '') {
            $query->where(fn ($q) => $q->whereNull('start_date')->orWhereDate('start_date', '<=', $filters['date_to']));
        }
        if ($states = $filters['states'] ?? []) {
            $today = now('Asia/Seoul')->format('Y-m-d');
            $query->whereIn(DB::raw("CASE WHEN status = 2 OR (end_date IS NOT NULL AND end_date < '$today') THEN 2 WHEN status = 0 OR (start_date IS NOT NULL AND start_date > '$today') THEN 0 ELSE 1 END"), $states);
        }
        $jointPurchases = $query->latest('id')->paginate((int) ($filters['per_page'] ?? 20))->withQueryString();

        return view('channel.sub03.joint_purchase_list', compact('jointPurchases', 'filters'));
    }

    private function form(?JointPurchase $joint = null)
    {
        $product = $joint?->product?->load('images') ?? new Product(['status' => 0, 'product_color' => '#000000', 'brand_id' => Brand::where('status', 1)->value('id'), 'product_price' => 0]);

        return view('channel.sub03.joint_purchase_form', [
            'joint' => $joint, 'product' => $product, 'categoryTree' => $this->categoryTreeForView(), 'categorySelection' => $this->categorySelectionForProduct($product),
            'productNoticeTemplates' => $this->productNoticeTemplates(), 'orderManagers' => $this->orderManagersForView(),
            'cancelRefundPolicies' => $this->cancelRefundPoliciesForView(Auth::guard('admin')->user()->vendor_id),
            'defaultRefundPolicy' => PlatformRefundPolicy::currentContent(), 'brands' => Brand::where('status', 1)->get(),
            'tiers' => $joint ? app(JointPurchasePricingService::class)->tiers($joint->id) : collect(),
        ]);
    }

    public function create()
    {
        return $this->form();
    }

    public function edit(int $id)
    {
        return $this->form($this->owned($id));
    }

    private function validated(Request $request, ?JointPurchase $joint): array
    {
        if ($request->filled('product_id')) {
            $selected = Product::where('vendor_id', Auth::guard('admin')->user()->vendor_id)->findOrFail($request->integer('product_id'));
            if ($joint && $selected->id !== $joint->product_id) {
                throw ValidationException::withMessages(['product_id' => '등록된 공동구매의 연결 상품은 변경할 수 없습니다.']);
            }
        }
        $data = $request->validate([
            'purchase_code' => ['required', 'string', 'max:80', Rule::unique('joint_purchases', 'purchase_code')->ignore($joint?->id)],
            'sale_name' => 'required|string|max:255', 'sale_price' => 'required|numeric|min:1|max:99999999', 'base_amount' => 'required|numeric|min:0|max:99999999',
            'detail_text' => 'nullable|string|max:200000', 'joint_status' => 'required|in:0,1,2', 'period_enabled' => 'required|boolean',
            'start_date' => 'nullable|required_if:period_enabled,1|date_format:Y-m-d',
            'end_date' => ['nullable', 'required_if:period_enabled,1', 'date_format:Y-m-d', ...($request->filled('start_date') ? ['after_or_equal:start_date'] : [])],
            'benefit_quantity' => 'nullable|array|max:20', 'benefit_quantity.*' => 'required|integer|min:1|max:100000000',
            'benefit_amount' => 'nullable|array|max:20', 'benefit_amount.*' => 'required|numeric|min:0.01|lte:sale_price',
        ]);
        if ($joint) {
            if ($request->hasAny(['benefit_quantity', 'benefit_amount', 'tier_min_quantity', 'tier_max_quantity', 'tier_unit_price'])) {
                throw ValidationException::withMessages(['benefit_quantity' => '등록한 판매 혜택은 수정할 수 없습니다.']);
            }
            if (OrdersProduct::where('joint_purchase_id', $joint->id)->exists() && ((float) $joint->discount_price !== (float) $data['sale_price'] || (float) $joint->product->product_price !== (float) $data['base_amount'])) {
                throw ValidationException::withMessages(['sale_price' => '주문 이력이 있는 공동구매의 가격은 변경할 수 없습니다.']);
            }
            if ($joint->benefits && max(array_column($joint->benefits, 'amount')) > (float) $data['sale_price']) {
                throw ValidationException::withMessages(['sale_price' => '판매가는 등록된 할인액보다 작을 수 없습니다.']);
            }
        }
        $quantities = $data['benefit_quantity'] ?? [];
        $amounts = $data['benefit_amount'] ?? [];
        if (count($quantities) !== count($amounts)) {
            throw ValidationException::withMessages(['benefit_amount' => '판매 수량과 혜택 금액을 함께 입력해 주세요.']);
        }
        if (count(array_unique($quantities)) !== count($quantities)) {
            throw ValidationException::withMessages(['benefit_quantity' => '판매 수량 기준은 중복할 수 없습니다.']);
        }
        $benefits = [];
        foreach ($quantities as $i => $qty) {
            $benefits[] = ['quantity' => (int) $qty, 'amount' => (float) $amounts[$i]];
        }
        usort($benefits, fn ($a, $b) => $a['quantity'] <=> $b['quantity']);
        for ($i = 1; $i < count($benefits); $i++) {
            if ($benefits[$i]['amount'] < $benefits[$i - 1]['amount']) {
                throw ValidationException::withMessages(['benefit_amount' => '수량이 늘어나는 구간의 할인액은 이전 구간 이상이어야 합니다.']);
            }
        }
        $request->merge(['product_code' => $joint?->product->product_code ?? 'JP-'.strtoupper(Str::random(16)),
            'product_price' => $data['base_amount'], 'sale_scope' => 'own', 'price_constraint_enabled' => 0, 'purchase_limit_enabled' => 0, 'stock_usage' => 'unused',
            'status' => (int) $data['joint_status'] === 1 ? 1 : 0, 'shipping_payment_type' => $request->input('shipping_payment_type', 'prepaid')]);
        $productData = $this->validateBaseProduct($request, ! $joint, $joint?->product_id);
        $this->validateAdditionalImageLimit($request, $joint?->product);

        return [$data, $productData, $joint?->benefits ?? $benefits];
    }

    private function syncBenefitPrices(JointPurchase $joint, array $benefits, float $price): void
    {
        $tiers = [];
        if (! $benefits || $benefits[0]['quantity'] > 1) {
            $tiers[] = ['min_quantity' => 1, 'max_quantity' => $benefits ? ($benefits[0]['quantity'] - 1) : null, 'unit_price' => $price];
        }
        foreach ($benefits as $i => $row) {
            $tiers[] = ['min_quantity' => $row['quantity'], 'max_quantity' => isset($benefits[$i + 1]) ? $benefits[$i + 1]['quantity'] - 1 : null, 'unit_price' => round($price - $row['amount'], 2)];
        }
        app(JointPurchasePricingService::class)->syncTiers($joint->id, $tiers);
    }

    private function save(Request $request, ?JointPurchase $joint = null)
    {
        [$data,$productData,$benefits] = $this->validated($request, $joint);
        try {
            DB::transaction(function () use ($request, $joint, $data, $productData, $benefits) {
                $admin = Auth::guard('admin')->user();
                if ($joint) {
                    $joint = JointPurchase::lockForUpdate()->findOrFail($joint->id);
                    $product = Product::lockForUpdate()->findOrFail($joint->product_id);
                    if (OrdersProduct::where('joint_purchase_id', $joint->id)->exists() && ((float) $joint->discount_price !== (float) $data['sale_price'] || (float) $product->product_price !== (float) $data['base_amount'])) {
                        throw ValidationException::withMessages(['sale_price' => '주문 이력이 있는 공동구매의 가격은 변경할 수 없습니다.']);
                    }
                } else {
                    $product = new Product;
                    $product->product_code = $productData['product_code'];
                    $product->vendor_id = $admin->vendor_id;
                    $product->admin_id = $admin->id;
                    $product->admin_type = 'vendor';
                    $product->parent_id = 0;
                    $product->is_featured = 'No';
                }
                $this->fillBaseProduct($product, $productData, $admin);
                $product->save();
                $joint = $joint ?? new JointPurchase(['product_id' => $product->id, 'current_quantity' => 0]);
                $priceChanged = ! $joint->exists || (float) $joint->discount_price !== (float) $data['sale_price'];
                $joint->fill(['purchase_code' => $data['purchase_code'], 'sale_name' => $data['sale_name'], 'discount_price' => $data['sale_price'],
                    'min_quantity' => $benefits ? max(array_column($benefits, 'quantity')) : ($joint->min_quantity ?? 1),
                    'status' => (int) $data['joint_status'], 'period_enabled' => (bool) $data['period_enabled'],
                    'start_date' => $data['period_enabled'] ? $data['start_date'] : null, 'end_date' => $data['period_enabled'] ? $data['end_date'] : null]);
                if (! $joint->exists) {
                    $joint->benefits = $benefits;
                }
                $joint->save();
                if ($priceChanged && $joint->benefits !== null) {
                    $this->syncBenefitPrices($joint, $benefits, (float) $data['sale_price']);
                }
                $this->storeProductDetailAssets($product, $request);
                $this->storeUploadedProductImages($product, $request);
            });
        } catch (\Throwable $error) {
            $this->discardRolledBackUploads();
            throw $error;
        }

        return redirect()->route('channel.joint_purchase.list')->with('success_message', '공동구매 상품이 저장되었습니다.');
    }

    public function store(Request $request)
    {
        return $this->save($request);
    }

    public function update(Request $request, int $id)
    {
        return $this->save($request, $this->owned($id));
    }

    public function destroy(int $id)
    {
        $joint = $this->owned($id);
        DB::transaction(function () use ($joint) {
            $joint = JointPurchase::lockForUpdate()->findOrFail($joint->id);
            if (JointPurchase::state($joint) !== 0) {
                throw ValidationException::withMessages(['delete' => '대기 상태의 공동구매만 삭제할 수 있습니다.']);
            }
            if (OrdersProduct::where('joint_purchase_id',$joint->id)->exists()) {
                throw ValidationException::withMessages(['delete' => '주문 이력이 있는 공동구매는 삭제할 수 없습니다.']);
            }
            $joint->delete();
        });

        return back()->with('success_message','공동구매 상품이 삭제되었습니다.');
    }
}
