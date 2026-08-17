<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Product; // 상품 모델 가정
use App\Models\Category;
use App\Models\OrdersProduct;
use App\Models\ProductsAttribute;
use App\Models\ProductsImage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class ChannelProductController extends Controller
{
    private const COMMISSION_VAT_MULTIPLIER = 1.1;

    public function storeOwnProduct(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            $validator = Validator::make($data, [
                'shop_id' => 'required|exists:shop_channels,id',
                'product_id' => 'required|exists:products,id',
                'selling_price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }

            try {
                // Get Current Vendor
                $admin = Auth::guard('admin')->user();
                if (!$admin || !$admin->vendor_id) {
                     return response()->json(['status' => false, 'message' => '판매자 권한이 없습니다.']);
                }

                // Verify Shop Channel belongs to Vendor
                $shop = \App\Models\ShopChannel::where('id', $data['shop_id'])
                    ->where('vendor_id', $admin->vendor_id)
                    ->first();
                if (!$shop) {
                    return response()->json(['status' => false, 'message' => '채널 권한이 없습니다.']);
                }

                // Verify Product belongs to Vendor
                $product = Product::where('id', $data['product_id'])
                    ->where('vendor_id', $admin->vendor_id)
                    ->first();
                if (!$product) {
                    return response()->json(['status' => false, 'message' => '상품 권한이 없습니다.']);
                }

                // Check if already added
                $exists = \App\Models\ShopChannelProduct::where('shop_channel_id', $shop->id)
                    ->where('product_id', $product->id)
                    ->exists();

                if ($exists) {
                    return response()->json(['status' => false, 'message' => '이미 채널에 추가된 상품입니다.']);
                }

                $pricing = $this->resolveShopProductPricing($product, $shop, (float) $data['selling_price'], 'own');

                // Create Mapping
                \App\Models\ShopChannelProduct::create(array_merge([
                    'shop_channel_id' => $shop->id,
                    'product_id' => $product->id,
                    'distributor_id' => $product->distributor_id,
                    'product_type' => 'own',
                    'approval_status' => 'approved',
                    'status' => 1, // 판매중 상태로 등록 (필요시 0으로)
                    'constraint_type' => 'none', // 추후 제약조건 폼 필드 연동 필요
                    'stock' => $product->stock,
                    'product_price' => $pricing['product_price'],
                    'selling_price' => $pricing['selling_price'],
                    'profit' => $pricing['profit'],
                ], $this->settlementSnapshot($shop, $pricing, 'seller')));

                return response()->json(['status' => true, 'message' => '상품이 성공적으로 채널에 추가되었습니다.']);

            } catch (ValidationException $e) {
                return response()->json(['status' => false, 'message' => collect($e->errors())->flatten()->first() ?: $e->getMessage()]);
            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
            }
        }
    }

    public function storePublicProduct(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            $validator = Validator::make($data, [
                'shop_id' => 'required|exists:shop_channels,id',
                'product_id' => 'required|exists:products,id',
                'selling_price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }

            try {
                $admin = Auth::guard('admin')->user();
                if (!$admin || !$admin->vendor_id) {
                     return response()->json(['status' => false, 'message' => '판매자 권한이 없습니다.']);
                }

                $shop = \App\Models\ShopChannel::where('id', $data['shop_id'])
                    ->where('vendor_id', $admin->vendor_id)
                    ->first();
                if (!$shop) {
                    return response()->json(['status' => false, 'message' => '채널 권한이 없습니다.']);
                }
                $this->assertOwnPgProductAllowed($shop, 'public');

                $product = Product::where('id', $data['product_id'])
                    ->first();
                if (!$product) {
                    return response()->json(['status' => false, 'message' => '상품 본체를 찾을 수 없습니다.']);
                }

                $exists = \App\Models\ShopChannelProduct::where('shop_channel_id', $shop->id)
                    ->where('product_id', $product->id)
                    ->exists();

                if ($exists) {
                    return response()->json(['status' => false, 'message' => '이미 채널에 추가된 상품입니다.']);
                }

                $pricing = $this->resolveShopProductPricing($product, $shop, (float) $data['selling_price'], 'public');

                \App\Models\ShopChannelProduct::create(array_merge([
                    'shop_channel_id' => $shop->id,
                    'product_id' => $product->id,
                    'distributor_id' => $product->distributor_id,
                    'product_type' => 'public',
                    'approval_status' => 'approved',
                    'status' => 1,
                    'constraint_type' => 'none',
                    'stock' => $product->stock,
                    'product_price' => $pricing['product_price'],
                    'selling_price' => $pricing['selling_price'],
                    'profit' => $pricing['profit'],
                ], $this->settlementSnapshot($shop, $pricing, $pricing['price_decider'] ?? 'reseller')));

                return response()->json(['status' => true, 'message' => '공유 상품이 성공적으로 추가되었습니다.']);
            } catch (ValidationException $e) {
                return response()->json(['status' => false, 'message' => collect($e->errors())->flatten()->first() ?: $e->getMessage()]);
            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
            }
        }
    }

    public function storePartialProduct(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
             
             $validator = Validator::make($data, [
                'shop_id' => 'required|exists:shop_channels,id',
                'product_id' => 'required|exists:products,id',
                'selling_price' => 'required|numeric|min:0',
                'request_reason' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }

            try {
                $admin = Auth::guard('admin')->user();
                if (!$admin || !$admin->vendor_id) {
                     return response()->json(['status' => false, 'message' => '판매자 권한이 없습니다.']);
                }
                
                $shop = \App\Models\ShopChannel::where('id', $data['shop_id'])
                    ->where('vendor_id', $admin->vendor_id)
                    ->first();
                if (!$shop) {
                    return response()->json(['status' => false, 'message' => '채널 권한이 없습니다.']);
                }
                $this->assertOwnPgProductAllowed($shop, 'partial');

                $product = Product::where('id', $data['product_id'])
                    ->first();
                if (!$product) {
                    return response()->json(['status' => false, 'message' => '상품을 찾을 수 없습니다.']);
                }

                $existingShopProduct = \App\Models\ShopChannelProduct::where('shop_channel_id', $shop->id)
                    ->where('product_id', $product->id)
                    ->where('product_type', 'partial')
                    ->first();

                if ($existingShopProduct && $existingShopProduct->approval_status === 'pending') {
                    return response()->json(['status' => false, 'message' => '이미 판매 권한 요청이 접수된 상품입니다. 승인 후 판매할 수 있습니다.']);
                }

                if ($existingShopProduct && $existingShopProduct->approval_status === 'rejected') {
                    return response()->json(['status' => false, 'message' => '판매 요청이 거부된 상품입니다. 재요청하기로 다시 신청해 주세요.']);
                }

                $pricing = $this->resolveShopProductPricing($product, $shop, (float) $data['selling_price'], 'partial');

                $shopProduct = $existingShopProduct ?: new \App\Models\ShopChannelProduct([
                    'shop_channel_id' => $shop->id,
                    'product_id' => $product->id,
                    'product_type' => 'partial',
                ]);

                $shopProduct->fill(array_merge([
                    'distributor_id' => $product->distributor_id,
                    'approval_status' => $existingShopProduct ? 'approved' : 'pending',
                    'request_reason' => $data['request_reason'] ?? null,
                    'requested_at' => $existingShopProduct?->requested_at ?? now(),
                    'status' => $existingShopProduct ? 1 : 0,
                    'constraint_type' => 'none',
                    'stock' => $product->stock,
                    'product_price' => $pricing['product_price'],
                    'selling_price' => $pricing['selling_price'],
                    'profit' => $pricing['profit'],
                ], $this->settlementSnapshot($shop, $pricing, $pricing['price_decider'] ?? 'reseller')));
                $shopProduct->save();

                $message = $existingShopProduct
                    ? '승인된 부분공유상품이 판매상품으로 추가되었습니다.'
                    : '판매 권한 요청이 성공적으로 접수되었습니다. (승인 대기)';
                return response()->json(['status' => true, 'message' => $message]);

            } catch (ValidationException $e) {
                return response()->json(['status' => false, 'message' => collect($e->errors())->flatten()->first() ?: $e->getMessage()]);
            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
            }
        }
    }


    public function requestPartialProduct(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['status' => false, 'message' => '잘못된 요청입니다.']);
        }

        $data = $request->all();
        $validator = Validator::make($data, [
            'shop_id' => 'required|exists:shop_channels,id',
            'product_id' => 'required|exists:products,id',
            'selling_price' => 'required|numeric|min:0',
            'request_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $admin = Auth::guard('admin')->user();
        if (!$admin || !$admin->vendor_id) {
            return response()->json(['status' => false, 'message' => '판매자 권한이 없습니다.']);
        }

        $shop = \App\Models\ShopChannel::where('id', $data['shop_id'])
            ->where('vendor_id', $admin->vendor_id)
            ->first();
        if (!$shop) {
            return response()->json(['status' => false, 'message' => '채널 권한이 없습니다.']);
        }

        $product = Product::where('id', $data['product_id'])
            ->where('is_partial', 'Yes')
            ->first();
        if (!$product) {
            return response()->json(['status' => false, 'message' => '판매요청 가능한 부분공개 상품을 찾을 수 없습니다.']);
        }
        if ($product->vendor_id == $admin->vendor_id) {
            return response()->json(['status' => false, 'message' => '본인 상품은 판매요청 없이 자사상품으로 추가해 주세요.']);
        }

        $shopProduct = \App\Models\ShopChannelProduct::firstOrNew([
            'shop_channel_id' => $shop->id,
            'product_id' => $product->id,
            'product_type' => 'partial',
        ]);

        $shopProduct->distributor_id = $product->distributor_id;
        $shopProduct->approval_status = 'pending';
        $shopProduct->request_reason = $data['request_reason'];
        $shopProduct->requested_at = now();
        $shopProduct->reviewed_at = null;
        $shopProduct->reviewed_by = null;
        $shopProduct->status = 0;
        $shopProduct->constraint_type = 'none';
        $shopProduct->stock = $product->stock ?? null;

        try {
            $this->assertOwnPgProductAllowed($shop, 'partial');
            $pricing = $this->resolveShopProductPricing($product, $shop, (float) $data['selling_price'], 'partial');
            $shopProduct->product_price = $pricing['product_price'];
            $shopProduct->selling_price = $pricing['selling_price'];
            $shopProduct->profit = $pricing['profit'];
            $shopProduct->fill($this->settlementSnapshot($shop, $pricing, $pricing['price_decider'] ?? 'reseller'));
            $shopProduct->save();
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => collect($e->errors())->flatten()->first() ?: $e->getMessage()]);
        }

        return response()->json(['status' => true, 'message' => '판매 권한 요청이 접수되었습니다.']);
    }

    public function updateProductStatus(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            $validator = Validator::make($data, [
                'shop_product_id' => 'required|exists:shop_channel_products,id',
                'status' => 'required|in:0,1', // 0: Stop, 1: Selling
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }

            try {
                $admin = Auth::guard('admin')->user();
                if (!$admin || !$admin->vendor_id) {
                     return response()->json(['status' => false, 'message' => '판매자 권한이 없습니다.']);
                }

                $shopProduct = \App\Models\ShopChannelProduct::find($data['shop_product_id']);
                
                // Verify ownership (the shop belongs to the vendor)
                if ($shopProduct->shopChannel->vendor_id != $admin->vendor_id) {
                    return response()->json(['status' => false, 'message' => '변경 권한이 없습니다.']);
                }

                $shopProduct->status = $data['status'];
                $shopProduct->save();

                $message = $data['status'] == 1 ? '판매가 재개되었습니다.' : '판매가 중지되었습니다.';
                return response()->json(['status' => true, 'message' => $message]);

            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
            }
        }
    }

    public function editShopProduct(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $shopProduct = \App\Models\ShopChannelProduct::with(['product' => function($query) {
            $query->with(['category', 'images', 'parentCategory']);
        }, 'shopChannel'])->findOrFail($id);

        // Verify ownership
        if ($shopProduct->shopChannel->vendor_id != $admin->vendor_id) {
            return redirect()->route('channel.shop_product01')->with('error_message', '권한이 없습니다.');
        }

        return view('channel.sub01.product_edit', [
            'dep1_id' => '01',
            'shopProduct' => $shopProduct,
            'product' => $shopProduct->product,
            'shopId' => $shopProduct->shop_channel_id,
            'settlementRate' => $shopProduct->shopChannel->settlement_rate ?? 10
        ]);
    }

    public function updateShopProduct(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $shopProduct = \App\Models\ShopChannelProduct::findOrFail($id);
        
        // Verify ownership
        $shop = \App\Models\ShopChannel::where('id', $shopProduct->shop_channel_id)
            ->where('vendor_id', $admin->vendor_id)
            ->firstOrFail();

        $request->validate([
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'nullable|numeric|min:0',
            'purchase_limit' => 'nullable|numeric|min:0',
            'status' => 'required|in:0,1',
        ]);

        $pricing = $this->resolveShopProductPricing($shopProduct->product, $shop, (float) $request->input('selling_price'), $shopProduct->product_type ?: 'own');
        $shopProduct->product_price = $pricing['product_price'];
        $shopProduct->selling_price = $pricing['selling_price'];
        $shopProduct->stock = $request->input('stock');
        $shopProduct->purchase_limit = $request->input('purchase_limit');
        $shopProduct->status = $request->input('status');
        
        $shopProduct->profit = $pricing['profit'];
        $shopProduct->fill($this->settlementSnapshot($shop, $pricing, $pricing['price_decider'] ?? 'seller'));
        
        $shopProduct->save();

        return redirect()->route('channel.shop_product01', ['shop_id' => $shop->id])
            ->with('success_message', '상품 정보가 성공적으로 수정되었습니다.');
    }
    public function getBaseProductDetail($id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return response()->json(['status' => false, 'message' => '로그인이 필요합니다.']);

        $product = Product::with([
            'category',
            'images',
            'brand',
            'parentCategory',
            'vendor.vendorbusinessdetails',
            'attributes' => function ($query) {
                $query->orderBy('id');
            },
        ])->find($id);
        if (!$product) return response()->json(['status' => false, 'message' => '상품을 찾을 수 없습니다.']);

        $isOwner = (int) $product->vendor_id === (int) $admin->vendor_id;
        $isPublic = in_array($product->is_public, ['Yes', 1, '1'], true);
        $isPartial = in_array($product->is_partial, ['Yes', 1, '1'], true);
        if (!$isOwner && !$isPublic && !$isPartial) {
            return response()->json(['status' => false, 'message' => '권한이 없습니다.']);
        }

        $activeOptions = $product->attributes->where('status', 1)->values();
        $stockLabel = $product->stock_usage === 'used'
            ? number_format((int) $activeOptions->sum('stock')) . ' 개'
            : '수량제한없음';
        $taxLabels = [
            'taxable' => '과세',
            'tax_free' => '면세',
            'zero_rated' => '영세',
        ];
        $priceCondition = number_format((float) $product->product_price) . ' 원';
        if ($product->price_constraint_enabled) {
            if ($product->price_constraint_type === 'fixed') {
                $priceCondition = number_format((float) $product->price_fixed) . ' 원';
            } elseif ($product->price_constraint_type === 'range') {
                $priceCondition = number_format((float) $product->price_min) . ' 원 ~ ' . number_format((float) $product->price_max) . ' 원';
            }
        }

        $profitShareLabel = '-';
        if ($product->profit_share_type === 'fixed') {
            $profitShareLabel = number_format((float) $product->profit_share_value) . ' 원';
        } elseif ($product->profit_share_type === 'percent') {
            $profitShareLabel = rtrim(rtrim(number_format((float) $product->profit_share_value, 2), '0'), '.') . ' %';
        }

        $purchaseLimitLabel = '-';
        if ($product->purchase_limit_enabled) {
            $min = $product->purchase_min_qty ? number_format((int) $product->purchase_min_qty) . ' 개 이상' : '';
            $max = $product->purchase_max_qty ? number_format((int) $product->purchase_max_qty) . ' 개 이하' : '';
            $purchaseLimitLabel = trim($min . ($min && $max ? ' / ' : '') . $max) ?: '-';
        }

        $detailHtml = $product->description ?: '등록된 상세 설명이 없습니다.';
        if ($product->detail_display_type === 'text' && $product->detail_text) {
            $detailHtml = $product->detail_text;
        } elseif ($product->detail_display_type === 'image' && ($product->detail_pc_image || $product->detail_mobile_image)) {
            $detailImages = collect([$product->detail_pc_image, $product->detail_mobile_image])
                ->filter()
                ->map(fn ($image) => '<p><img src="' . e(asset('front/images/product_detail_images/' . $image)) . '" style="max-width:100%; height:auto;"></p>')
                ->implode('');
            $detailHtml = $detailImages ?: $detailHtml;
        }

        $productArray = $product->toArray();
        $productArray['seller_name'] = $product->vendor?->vendorbusinessdetails?->shop_name ?? ($product->vendor?->name ?? '-');
        $productArray['tax_label'] = $taxLabels[$product->tax_type] ?? '과세';
        $productArray['reward_points_label'] = number_format((int) ($product->reward_points ?? 0)) . ' point';
        $productArray['price_condition_label'] = $priceCondition;
        $productArray['profit_share_label'] = $profitShareLabel;
        $productArray['stock_label'] = $stockLabel;
        $productArray['purchase_limit_label'] = $purchaseLimitLabel;
        $productArray['detail_html'] = $detailHtml;
        $productArray['image_urls'] = $product->images->pluck('image')
            ->filter()
            ->map(fn ($image) => asset('front/images/product_images/small/' . $image))
            ->values();
        $productArray['option_rows'] = $activeOptions->map(function ($option) use ($product) {
            return [
                'name' => $option->option_name ?: '옵션',
                'type' => $option->option_type ?: 'general',
                'value' => $option->size,
                'sku' => $option->sku,
                'price' => (float) ($option->price ?? $product->product_price),
                'stock' => (int) $option->stock,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'product' => $productArray,
            'category_path' => $product->category_path
        ]);
    }

    private function resolveShopProductPricing(Product $product, \App\Models\ShopChannel $shop, float $sellingPrice, string $productType): array
    {
        $this->assertOwnPgProductAllowed($shop, $productType);

        $supplyPrice = (float) $product->product_price;
        $shippingFee = $this->productShippingFee($product);
        $rewardPoints = max(0, (float) ($product->reward_points ?? 0));
        $settlementType = (int) ($shop->settlement_type ?: 1);
        $settlementRate = (float) ($shop->settlement_rate ?? 0);
        $isShared = in_array($productType, ['public', 'partial'], true);
        $isFixed = $isShared
            && (bool) ($product->price_constraint_enabled ?? false)
            && $product->price_constraint_type === 'fixed';

        if ($isFixed) {
            $fixedPrice = (float) ($product->price_fixed ?: $product->product_price);
            if (round($sellingPrice, 2) !== round($fixedPrice, 2)) {
                throw ValidationException::withMessages([
                    'selling_price' => '판매가 고정 공유상품은 ' . number_format($fixedPrice) . '원으로만 등록할 수 있습니다.',
                ]);
            }

            $commission = $this->shopCommissionAmount($fixedPrice + $shippingFee, 1, $settlementType, $settlementRate);
            $rebate = $this->productRebateAmount($product, $fixedPrice + $shippingFee);
            $minimumRebate = $commission + $rewardPoints;
            if ($rebate < $minimumRebate) {
                throw ValidationException::withMessages([
                    'selling_price' => '판매가 고정 공유상품의 리베이트는 수수료와 지급 포인트를 포함해 ' . number_format($minimumRebate) . '원 이상이어야 합니다.',
                ]);
            }

            return [
                'product_price' => $fixedPrice,
                'selling_price' => $fixedPrice,
                'minimum_price' => $fixedPrice,
                'profit' => round($rebate - $commission - $rewardPoints, 2),
                'maximum_reward_points' => max(0, (int) round($rebate - $commission)),
                'price_decider' => 'supplier',
            ];
        }

        $minimumPrice = $this->minimumSellingPrice($product, $shop);
        $needsCommonPgMinimum = !$shop->use_own_pg || $rewardPoints > 0 || $isShared;
        if ($needsCommonPgMinimum && $sellingPrice < $minimumPrice) {
            throw ValidationException::withMessages([
                'selling_price' => '최소 판매가는 ' . number_format($minimumPrice) . '원입니다. 수수료와 지급 포인트를 반영한 금액 이상으로 입력해 주세요.',
            ]);
        }

        if ($isShared
            && (bool) ($product->price_constraint_enabled ?? false)
            && $product->price_constraint_type === 'range'
            && $product->price_max !== null
            && $sellingPrice > (float) $product->price_max) {
            throw ValidationException::withMessages([
                'selling_price' => '판매가는 공유자가 설정한 최대 금액 ' . number_format((float) $product->price_max) . '원을 초과할 수 없습니다.',
            ]);
        }

        $commission = $this->shopCommissionAmount($sellingPrice + $shippingFee, 1, $settlementType, $settlementRate);

        return [
            'product_price' => $supplyPrice,
            'selling_price' => $sellingPrice,
            'minimum_price' => $minimumPrice,
            'profit' => round($sellingPrice + $shippingFee - $supplyPrice - $shippingFee - $commission - $rewardPoints, 2),
            'maximum_reward_points' => max(0, (int) round($sellingPrice - $supplyPrice - $commission)),
            'price_decider' => $isShared ? 'reseller' : 'seller',
        ];
    }

    private function assertOwnPgProductAllowed(\App\Models\ShopChannel $shop, string $productType): void
    {
        if ($shop->use_own_pg && in_array($productType, ['public', 'partial'], true)) {
            throw ValidationException::withMessages([
                'product_type' => '자사 PG를 사용하는 Shop 채널은 자사상품만 판매할 수 있습니다.',
            ]);
        }
    }

    private function settlementSnapshot(\App\Models\ShopChannel $shop, array $pricing, string $priceDecider): array
    {
        return [
            'settlement_type_snapshot' => (int) ($shop->settlement_type ?: 1),
            'settlement_rate_snapshot' => (float) ($shop->settlement_rate ?? 0),
            'minimum_selling_price' => $pricing['minimum_price'] ?? null,
            'maximum_reward_points' => (int) ($pricing['maximum_reward_points'] ?? max(0, (int) round($pricing['profit'] ?? 0))),
            'price_decider' => $priceDecider,
        ];
    }

    private function minimumSellingPrice(Product $product, \App\Models\ShopChannel $shop): float
    {
        $supplyPrice = (float) $product->product_price;
        $shippingFee = $this->productShippingFee($product);
        $rewardPoints = max(0, (float) ($product->reward_points ?? 0));
        $settlementType = (int) ($shop->settlement_type ?: 1);
        $settlementRate = (float) ($shop->settlement_rate ?? 0);

        if ($settlementType === 2) {
            $minimum = $supplyPrice + $this->shopCommissionAmount(0, 1, $settlementType, $settlementRate) + $rewardPoints;
        } else {
            $rate = ($settlementRate / 100) * self::COMMISSION_VAT_MULTIPLIER;
            $minimum = $rate >= 1
                ? $supplyPrice
                : (($supplyPrice + $shippingFee + $rewardPoints) / (1 - $rate)) - $shippingFee;
        }

        if ((bool) ($product->price_constraint_enabled ?? false) && $product->price_constraint_type === 'range') {
            $minimum = max($minimum, (float) ($product->price_min ?? 0));
        }

        return $this->ceilToTen($minimum);
    }

    private function shopCommissionAmount(float $grossAmount, int $quantity, int $settlementType, float $settlementRate): float
    {
        $amount = $settlementType === 2
            ? $quantity * $settlementRate
            : $grossAmount * ($settlementRate / 100) * self::COMMISSION_VAT_MULTIPLIER;

        return $this->ceilToTen(max(0, $amount));
    }

    private function productRebateAmount(Product $product, float $grossAmount): float
    {
        $value = (float) ($product->profit_share_value ?? 0);
        if ($product->profit_share_type === 'percent') {
            return round($grossAmount * ($value / 100), 2);
        }

        if ($product->profit_share_type === 'fixed') {
            return round($value, 2);
        }

        return 0;
    }

    private function ceilToTen(float $amount): float
    {
        return ceil(max(0, $amount) / 10) * 10;
    }

    private function productShippingFee(Product $product): float
    {
        if (in_array($product->shipping_policy_type ?? null, ['paid', 'free_conditional'], true)) {
            return max(0, (float) ($product->shipping_base_fee ?? 0));
        }

        return 0;
    }


    public function exportOwnProducts(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $query = Product::where('vendor_id', $admin->vendor_id)
            ->with(['category'])
            ->withCount([
                'shopChannelProducts as shop_channels_count' => function ($query) {
                    $query->where('approval_status', 'approved');
                },
                'shopChannelProducts as sales_request_count' => function ($query) {
                    $query->where('product_type', 'partial');
                },
            ]);

        $keyword = trim((string) $request->input('q', ''));
        if ($keyword !== '') {
            $query->where(function ($search) use ($keyword) {
                $search->where('product_name', 'like', '%' . $keyword . '%')
                    ->orWhere('product_code', 'like', '%' . $keyword . '%');
            });
        }

        $categoryId = (int) $request->input('category_id', 0);
        if ($categoryId > 0) {
            $query->whereIn('category_id', $this->categoryWithDescendantIds($categoryId));
        }

        $status = $request->input('status', '');
        if ($status === 'stop_notice') {
            $query->whereNotNull('stop_notice_at');
        } elseif (in_array((string) $status, ['0', '1'], true)) {
            $query->where('status', (int) $status);
        }

        $saleScope = $request->input('sale_scope', '');
        if ($saleScope === 'own') {
            $query->where('is_public', 'No')->where('is_partial', 'No');
        } elseif ($saleScope === 'public') {
            $query->where('is_public', 'Yes');
        } elseif ($saleScope === 'partial') {
            $query->where('is_partial', 'Yes');
        }

        if ($request->filled('price_min') && is_numeric($request->input('price_min'))) {
            $query->where('product_price', '>=', (float) $request->input('price_min'));
        }
        if ($request->filled('price_max') && is_numeric($request->input('price_max'))) {
            $query->where('product_price', '<=', (float) $request->input('price_max'));
        }

        $products = $query->orderByDesc('id')->get();

        $filename = 'own-products-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($products) {
            echo "\xEF\xBB\xBF";
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['상품코드', '상품상태', '상품명', '카테고리', '금액', '공개여부', '부분공개', '게시채널', '판매요청', '판매중지 예고일']);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->product_code,
                    $product->status == 1 ? '판매' : '중지',
                    $product->product_name,
                    $product->category_path,
                    (int) $product->product_price,
                    in_array($product->is_public, ['Yes', 1, '1'], true) ? '공개' : '비공개',
                    in_array($product->is_partial, ['Yes', 1, '1'], true) ? '부분공개' : '전체공개',
                    $product->shop_channels_count,
                    $product->sales_request_count,
                    optional($product->stop_notice_at)->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function categoryWithDescendantIds(int $categoryId): array
    {
        $ids = [$categoryId];
        $children = Category::where('parent_id', $categoryId)->pluck('id')->all();
        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->categoryWithDescendantIds((int) $childId));
        }

        return array_values(array_unique($ids));
    }

    public function productCategories()
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $categories = $this->baseProductCategories();

        return view('channel.sub02.product_categories', [
            'dep1_id' => '02',
            'categories' => $categories,
            'categoryTree' => $this->categoryTreeForView($categories),
        ]);
    }

    public function storeProductCategory(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $data = $request->validate([
            'level' => 'required|in:middle,minor',
            'major_category_id' => 'required|exists:categories,id',
            'middle_category_id' => 'nullable|exists:categories,id',
            'category_name' => 'required|string|max:100',
        ]);

        $major = Category::findOrFail($data['major_category_id']);
        if ((int) $major->parent_id !== 0) {
            return redirect()->back()->with('error_message', '대분류를 올바르게 선택해 주세요.')->withInput();
        }

        $parent = $major;
        if ($data['level'] === 'minor') {
            $parent = Category::find((int) ($data['middle_category_id'] ?? 0));
            if (!$parent || (int) $parent->parent_id !== $major->id) {
                return redirect()->back()->with('error_message', '소분류를 등록할 중분류를 선택해 주세요.')->withInput();
            }
        }

        $this->createChannelCategory($data['category_name'], $parent, $admin->vendor_id);

        return redirect()->route('channel.product.categories')->with('success_message', '분류가 등록되었습니다.');
    }

    public function updateProductCategory(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $data = $request->validate([
            'category_name' => 'required|string|max:100',
            'status' => 'required|in:0,1',
        ]);

        $category = $this->editableChannelCategory($id, $admin->vendor_id);
        if (!$category) {
            return redirect()->back()->with('error_message', '수정 가능한 분류를 찾을 수 없습니다.');
        }

        $duplicate = Category::where('parent_id', $category->parent_id)
            ->where('category_name', trim($data['category_name']))
            ->where('id', '!=', $category->id);
        $this->applyChannelCategoryScope($duplicate, $admin->vendor_id);
        if ($duplicate->exists()) {
            return redirect()->back()->with('error_message', '같은 상위 분류에 이미 등록된 이름입니다.')->withInput();
        }

        $category->category_name = trim($data['category_name']);
        $category->status = (int) $data['status'];
        $category->meta_title = $category->category_name;
        $category->meta_description = $category->category_name;
        $category->meta_keywords = $category->category_name;
        $category->save();

        return redirect()->route('channel.product.categories')->with('success_message', '분류가 수정되었습니다.');
    }

    public function deleteProductCategory($id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $category = $this->editableChannelCategory($id, $admin->vendor_id);
        if (!$category) {
            return redirect()->back()->with('error_message', '삭제 가능한 분류를 찾을 수 없습니다.');
        }

        if (Category::where('parent_id', $category->id)->exists()) {
            return redirect()->back()->with('error_message', '하위 분류가 있는 분류는 삭제할 수 없습니다.');
        }

        if (Product::where('category_id', $category->id)->exists()) {
            return redirect()->back()->with('error_message', '상품에 사용 중인 분류는 삭제할 수 없습니다.');
        }

        $category->delete();

        return redirect()->route('channel.product.categories')->with('success_message', '분류가 삭제되었습니다.');
    }

    public function createBaseProduct()
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $product = new Product([
            'product_code' => $this->generateProductCode($admin->vendor_id),
            'product_color' => '#000000',
            'product_discount' => 0,
            'product_weight' => 1,
            'is_public' => 'No',
            'is_partial' => 'No',
            'status' => 1,
        ]);

        return view('channel.sub02.product_base_edit', [
            'dep1_id' => '02',
            'product' => $product,
            'categories' => $this->baseProductCategories(),
            'categoryTree' => $this->categoryTreeForView(),
            'categorySelection' => $this->categorySelectionForProduct($product),
            'productOptions' => $this->productOptionsForView($product),
            'productNoticeTemplates' => $this->productNoticeTemplates(),
            'productNoticeItems' => old('product_notice_items', $product->product_notice_items ?? []),
            'sections' => \App\Models\Section::where('status', 1)->get(),
            'brands' => \App\Models\Brand::where('status', 1)->orderBy('name')->get(),
            'orderManagers' => $this->orderManagersForView(),
            'cancelRefundPolicies' => $this->cancelRefundPoliciesForView($admin->vendor_id),
            'shippingPolicyOptions' => $this->shippingPolicyOptions(),
            'isCreate' => true,
        ]);
    }

    public function storeBaseProduct(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $data = $this->validateBaseProduct($request, true);
        $this->validateAdditionalImageLimit($request);

        $product = new Product();
        $this->fillBaseProduct($product, $data, $admin);
        $product->product_code = $data['product_code'];
        $product->vendor_id = $admin->vendor_id;
        $product->admin_id = $admin->id;
        $product->admin_type = 'vendor';
        $product->parent_id = 0;
        $product->is_featured = 'No';
        if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'is_bestseller')) {
            $product->is_bestseller = 'No';
        }
        $product->partial_approved = 'Approved';
        $product->save();
        $this->syncProductOptions($product, $request);
        $this->storeProductDetailAssets($product, $request);
        $this->storeUploadedProductImages($product, $request);

        return redirect()->route('channel.product_own')->with('success_message', '자사 상품이 등록되었습니다.');
    }

    public function editBaseProduct($id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $product = Product::with(['category', 'images', 'brand', 'attributes'])->findOrFail($id);

        // Verify ownership
        if ($product->vendor_id != $admin->vendor_id) {
            return redirect()->route('channel.product_own')->with('error_message', '권한이 없습니다.');
        }

        $categories = $this->baseProductCategories();
        $sections = \App\Models\Section::where('status', 1)->get();

        return view('channel.sub02.product_base_edit', [
            'dep1_id' => '02',
            'product' => $product,
            'categories' => $categories,
            'categoryTree' => $this->categoryTreeForView($categories),
            'categorySelection' => $this->categorySelectionForProduct($product),
            'productOptions' => $this->productOptionsForView($product),
            'productNoticeTemplates' => $this->productNoticeTemplates(),
            'productNoticeItems' => old('product_notice_items', $product->product_notice_items ?? []),
            'sections' => $sections,
            'brands' => \App\Models\Brand::where('status', 1)->orderBy('name')->get(),
            'orderManagers' => $this->orderManagersForView(),
            'cancelRefundPolicies' => $this->cancelRefundPoliciesForView($admin->vendor_id),
            'shippingPolicyOptions' => $this->shippingPolicyOptions(),
            'isCreate' => false,
        ]);
    }

    public function updateBaseProduct(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $product = Product::findOrFail($id);

        // Verify ownership
        if ($product->vendor_id != $admin->vendor_id) {
            return redirect()->route('channel.product_own')->with('error_message', '권한이 없습니다.');
        }

        $data = $this->validateBaseProduct($request, false, $product->id);
        $this->validateAdditionalImageLimit($request, $product);
        $this->fillBaseProduct($product, $data, $admin);
        $product->save();
        $this->syncProductOptions($product, $request);
        $this->storeProductDetailAssets($product, $request);
        $this->storeUploadedProductImages($product, $request);

        return redirect()->route('channel.product_own')->with('success_message', '상품 정보가 성공적으로 수정되었습니다.');
    }

    private function validateBaseProduct(Request $request, bool $isCreate, ?int $productId = null): array
    {
        $uniqueRule = 'unique:products,product_code';
        if (!$isCreate && $productId) {
            $uniqueRule .= ',' . $productId;
        }
        $admin = Auth::guard('admin')->user();
        $vendorId = (int) ($admin->vendor_id ?? 0);
        $distributorRule = Schema::hasColumn('distributors', 'vendor_id')
            ? Rule::exists('distributors', 'id')->where(fn ($query) => $query->where('vendor_id', $vendorId))
            : 'exists:distributors,id';

        $data = $request->validate([
            'product_code' => [$isCreate ? 'required' : 'nullable', 'string', 'max:80', $uniqueRule],
            'product_name' => 'required|string|max:255',
            'section_id' => 'nullable|exists:sections,id',
            'category_id' => 'nullable|exists:categories,id',
            'major_category_id' => 'required|exists:categories,id',
            'middle_category_id' => 'required|exists:categories,id',
            'minor_category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'product_price' => 'required|numeric|min:0',
            'sale_scope' => 'required|in:own,public,affiliate',
            'reward_points' => 'nullable|integer|min:0',
            'tax_type' => 'required|in:taxable,tax_free,zero_rated',
            'price_constraint_enabled' => 'required|in:0,1',
            'price_constraint_type' => 'nullable|required_if:price_constraint_enabled,1|in:range,fixed',
            'price_min' => 'nullable|required_if:price_constraint_type,range|numeric|min:0',
            'price_max' => 'nullable|required_if:price_constraint_type,range|numeric|min:0',
            'price_fixed' => 'nullable|required_if:price_constraint_type,fixed|numeric|min:0',
            'profit_share_type' => 'nullable|in:none,fixed,percent',
            'profit_share_value' => 'nullable|numeric|min:0',
            'purchase_limit_enabled' => 'required|in:0,1',
            'purchase_min_qty' => 'nullable|required_if:purchase_limit_enabled,1|integer|min:1',
            'purchase_max_qty' => 'nullable|required_if:purchase_limit_enabled,1|integer|min:1',
            'stock_usage' => 'required|in:unused,used',
            'product_discount' => 'nullable|numeric|min:0|max:100',
            'product_weight' => 'nullable|integer|min:0',
            'product_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'description' => 'nullable|string',
            'detail_display_type' => 'required|in:unused,image,text',
            'detail_text' => 'nullable|required_if:detail_display_type,text|string',
            'detail_pc_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:10240',
            'detail_mobile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:10240',
            'order_manager_enabled' => 'required|in:0,1',
            'distributor_id' => ['nullable', 'required_if:order_manager_enabled,1', $distributorRule],
            'shipping_policy_type' => 'required|in:free,free_conditional,paid',
            'shipping_policy_name' => 'nullable|string|max:120',
            'shipping_payment_type' => 'required|in:prepaid,collect',
            'shipping_base_fee' => 'nullable|required_unless:shipping_policy_type,free|numeric|min:0',
            'shipping_free_threshold' => 'nullable|required_if:shipping_policy_type,free_conditional|numeric|min:0',
            'cancel_refund_policy_id' => 'nullable|exists:shop_cancel_refund_policies,id',
            'status' => 'required|in:0,1',
            'is_public' => 'nullable|in:0,1,No,Yes',
            'is_partial' => 'nullable|in:0,1,No,Yes',
            'product_notice_type' => 'nullable|string|max:80',
            'product_notice_items' => 'nullable|array',
            'product_notice_items.*' => 'nullable|string|max:2000',
            'product_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'images' => 'nullable|array|max:20',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'slot_image_ids' => 'nullable|array|max:20',
            'slot_image_ids.*' => 'nullable|integer|exists:products_images,id',
            'slot_images' => 'nullable|array|max:20',
            'slot_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'option_ids' => 'nullable|array|max:50',
            'option_ids.*' => 'nullable|integer|exists:products_attributes,id',
            'option_names' => 'nullable|array|max:50',
            'option_names.*' => 'nullable|string|max:80',
            'option_types' => 'nullable|array|max:50',
            'option_types.*' => 'nullable|in:text,general,price',
            'option_values' => 'nullable|array|max:50',
            'option_values.*' => 'nullable|string|max:120',
            'option_skus' => 'nullable|array|max:50',
            'option_skus.*' => 'nullable|string|max:120',
            'option_prices' => 'nullable|array|max:50',
            'option_prices.*' => 'nullable|numeric|min:0',
            'option_stocks' => 'nullable|array|max:50',
            'option_stocks.*' => 'nullable|integer|min:0',
            'option_statuses' => 'nullable|array|max:50',
            'option_statuses.*' => 'nullable|in:0,1',
        ]);

        if (($data['price_constraint_type'] ?? null) === 'range'
            && isset($data['price_min'], $data['price_max'])
            && (float) $data['price_min'] > (float) $data['price_max']) {
            throw ValidationException::withMessages(['price_max' => '판매금액 범위의 최대 금액은 최소 금액보다 커야 합니다.']);
        }

        if ((int) ($data['price_constraint_enabled'] ?? 0) === 1) {
            $profitShareType = $data['profit_share_type'] ?? 'none';
            if ($profitShareType !== 'none' && ($data['profit_share_value'] ?? '') === '') {
                throw ValidationException::withMessages(['profit_share_value' => '수익배분 값을 입력해 주세요.']);
            }
            if ($profitShareType === 'percent' && isset($data['profit_share_value']) && (float) $data['profit_share_value'] > 100) {
                throw ValidationException::withMessages(['profit_share_value' => '정률 수익배분은 100% 이하로 입력해 주세요.']);
            }
            if (($data['price_constraint_type'] ?? null) === 'fixed' && in_array($data['sale_scope'] ?? 'own', ['public', 'affiliate'], true)) {
                if ((int) ($data['reward_points'] ?? 0) > 0) {
                    throw ValidationException::withMessages(['reward_points' => '판매가 고정 공유상품은 포인트를 제공할 수 없습니다.']);
                }

                $fixedPrice = (float) ($data['price_fixed'] ?? 0);
                $minimumRebate = round($fixedPrice * 0.05 * self::COMMISSION_VAT_MULTIPLIER, 2);
                $rebate = $profitShareType === 'percent'
                    ? round($fixedPrice * ((float) ($data['profit_share_value'] ?? 0) / 100), 2)
                    : (float) ($data['profit_share_value'] ?? 0);

                if ($rebate < $minimumRebate) {
                    throw ValidationException::withMessages([
                        'profit_share_value' => '판매가 고정 공유상품의 리베이트는 기본 수수료 ' . number_format($minimumRebate) . '원 이상이어야 합니다.',
                    ]);
                }
            }
        }

        if ((int) ($data['purchase_limit_enabled'] ?? 0) === 1
            && isset($data['purchase_min_qty'], $data['purchase_max_qty'])
            && (int) $data['purchase_min_qty'] > (int) $data['purchase_max_qty']) {
            throw ValidationException::withMessages(['purchase_max_qty' => '최대 구매수량은 최소 구매수량보다 커야 합니다.']);
        }

        $vendorId = Auth::guard('admin')->user()->vendor_id ?? null;
        if (!empty($data['cancel_refund_policy_id']) && $vendorId) {
            $policyExists = \App\Models\ShopCancelRefundPolicy::where('id', $data['cancel_refund_policy_id'])
                ->where('vendor_id', $vendorId)
                ->where('status', 'active')
                ->exists();
            if (!$policyExists) {
                throw ValidationException::withMessages(['cancel_refund_policy_id' => '사용 가능한 취소/환불 안내를 선택해 주세요.']);
            }
        }

        if (($data['detail_display_type'] ?? null) === 'image' && Schema::hasColumn('products', 'detail_pc_image')) {
            $existingProduct = $productId ? Product::find($productId) : null;
            $hasPcImage = ($existingProduct && $existingProduct->detail_pc_image)
                || ($request->hasFile('detail_pc_image') && $request->file('detail_pc_image')->isValid());
            $hasMobileImage = ($existingProduct && $existingProduct->detail_mobile_image)
                || ($request->hasFile('detail_mobile_image') && $request->file('detail_mobile_image')->isValid());

            $messages = [];
            if (!$hasPcImage) {
                $messages['detail_pc_image'] = 'PC 상세 이미지를 등록해 주세요.';
            }
            if (!$hasMobileImage) {
                $messages['detail_mobile_image'] = 'Mobile 상세 이미지를 등록해 주세요.';
            }
            if ($messages) {
                throw ValidationException::withMessages($messages);
            }
        }

        return $this->resolveBaseProductCategory($data);
    }

    private function resolveBaseProductCategory(array $data): array
    {
        $majorId = (int) ($data['major_category_id'] ?? 0);
        $middleId = (int) ($data['middle_category_id'] ?? 0);
        $minorId = (int) ($data['minor_category_id'] ?? 0);

        if ($majorId) {
            $vendorId = Auth::guard('admin')->user()->vendor_id ?? null;
            $major = Category::find($majorId);
            if (!$major || (int) $major->parent_id !== 0) {
                throw ValidationException::withMessages(['major_category_id' => '대분류를 올바르게 선택해 주세요.']);
            }

            $middleQuery = Category::where('id', $middleId);
            $this->applyChannelCategoryScope($middleQuery, $vendorId);
            $middle = $middleQuery->first();
            if (!$middle || (int) $middle->parent_id !== $major->id) {
                throw ValidationException::withMessages(['middle_category_id' => '대분류에 등록된 중분류를 선택해 주세요.']);
            }

            $finalCategory = $middle;
            if ($minorId) {
                $minorQuery = Category::where('id', $minorId);
                $this->applyChannelCategoryScope($minorQuery, $vendorId);
                $minor = $minorQuery->first();
                if (!$minor || (int) $minor->parent_id !== $middle->id) {
                    throw ValidationException::withMessages(['minor_category_id' => '중분류에 등록된 소분류를 선택해 주세요.']);
                }
                $finalCategory = $minor;
            }

            $data['category_id'] = $finalCategory->id;
            $data['section_id'] = $major->section_id ?: ($data['section_id'] ?? null);
            $data = $this->resolveProductNotice($data, $major->category_name);

            return $data;
        }
        throw ValidationException::withMessages(['major_category_id' => '상품분류의 대분류를 선택해 주세요.']);
    }

    private function resolveProductNotice(array $data, ?string $majorCategoryName): array
    {
        $noticeType = $this->productNoticeTypeForCategoryName($majorCategoryName);
        $templates = $this->productNoticeTemplates();
        $requestItems = $data['product_notice_items'] ?? [];

        if (!$noticeType || !isset($templates[$noticeType])) {
            $data['product_notice_type'] = null;
            $data['product_notice_items'] = null;
            return $data;
        }

        $items = [];
        foreach ($templates[$noticeType]['fields'] as $field) {
            $key = $field['key'];
            $items[$key] = trim((string) ($requestItems[$key] ?? ''));
        }

        $data['product_notice_type'] = $noticeType;
        $data['product_notice_items'] = $items;

        return $data;
    }

    private function baseProductCategories()
    {
        $noticeCategoryNames = array_keys($this->productNoticeMajorCategories());
        $vendorId = Auth::guard('admin')->user()->vendor_id ?? null;

        $categories = Category::with(['subCategories' => function ($query) use ($vendorId) {
                $this->applyChannelCategoryScope($query, $vendorId);
                $query->where('status', 1)->with(['subCategories' => function ($subQuery) use ($vendorId) {
                    $this->applyChannelCategoryScope($subQuery, $vendorId);
                    $subQuery->where('status', 1)->orderBy('category_name');
                }])->orderBy('category_name');
            }])
            ->where('parent_id', 0)
            ->where('status', 1)
            ->whereIn('category_name', $noticeCategoryNames)
            ->orderBy('category_name')
            ->get();

        $noticeOrder = array_flip(array_keys($this->productNoticeMajorCategories()));

        return $categories->sortBy(function ($category) use ($noticeOrder) {
            return $noticeOrder[$category->category_name] ?? (1000 + $category->id);
        })->values();
    }

    private function applyChannelCategoryScope($query, ?int $vendorId): void
    {
        if (!Schema::hasColumn('categories', 'vendor_id')) {
            return;
        }

        $query->where(function ($scope) use ($vendorId) {
            $scope->whereNull('vendor_id');
            if ($vendorId) {
                $scope->orWhere('vendor_id', $vendorId);
            }
        });
    }

    private function findOrCreateProductCategory(string $name, Category $parent): Category
    {
        $name = trim($name);
        $category = Category::where('parent_id', $parent->id)
            ->where('category_name', $name)
            ->first();

        if ($category) {
            if ((int) $category->status !== 1) {
                $category->status = 1;
                $category->save();
            }
            return $category;
        }

        $category = new Category();
        $category->parent_id = $parent->id;
        $category->section_id = $parent->section_id;
        $category->category_name = $name;
        $category->category_image = '';
        $category->category_discount = 0;
        $category->description = '';
        $category->url = $this->uniqueCategoryUrl(Str::slug($name) ?: 'category');
        $category->meta_title = $name;
        $category->meta_description = $name;
        $category->meta_keywords = $name;
        $category->status = 1;
        $category->save();

        return $category;
    }

    private function createChannelCategory(string $name, Category $parent, ?int $vendorId): Category
    {
        $name = trim($name);
        if ($name === '') {
            throw ValidationException::withMessages(['category_name' => '분류명을 입력해 주세요.']);
        }

        $query = Category::where('parent_id', $parent->id)
            ->where('category_name', $name);
        $this->applyChannelCategoryScope($query, $vendorId);

        if ($query->exists()) {
            throw ValidationException::withMessages(['category_name' => '같은 상위 분류에 이미 등록된 이름입니다.']);
        }

        $category = new Category();
        $category->parent_id = $parent->id;
        $category->section_id = $parent->section_id;
        if (Schema::hasColumn('categories', 'vendor_id')) {
            $category->vendor_id = $vendorId;
        }
        $category->category_name = $name;
        $category->category_image = '';
        $category->category_discount = 0;
        $category->description = '';
        $category->url = $this->uniqueCategoryUrl(Str::slug($name) ?: 'category');
        $category->meta_title = $name;
        $category->meta_description = $name;
        $category->meta_keywords = $name;
        $category->status = 1;
        $category->save();

        return $category;
    }

    private function editableChannelCategory($id, ?int $vendorId): ?Category
    {
        $category = Category::find($id);
        if (!$category || (int) $category->parent_id === 0) {
            return null;
        }

        if (Schema::hasColumn('categories', 'vendor_id') && (int) $category->vendor_id !== (int) $vendorId) {
            return null;
        }

        return $category;
    }

    private function uniqueCategoryUrl(string $baseUrl): string
    {
        $baseUrl = trim($baseUrl) ?: 'category';
        $candidate = $baseUrl;
        $suffix = 2;

        while (Category::where('url', $candidate)->exists()) {
            $candidate = $baseUrl . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function categoryTreeForView($categories = null): array
    {
        $categories = $categories ?: $this->baseProductCategories();

        return $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->category_name,
                'children' => $category->subCategories->map(function ($middle) {
                    return [
                        'id' => $middle->id,
                        'name' => $middle->category_name,
                        'children' => $middle->subCategories->map(function ($minor) {
                            return [
                                'id' => $minor->id,
                                'name' => $minor->category_name,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        })->values()->toArray();
    }

    private function categorySelectionForProduct(Product $product): array
    {
        if (!$product->category_id) {
            return ['major' => null, 'middle' => null, 'minor' => null, 'middle_name' => '', 'minor_name' => '', 'final' => null];
        }

        $category = Category::find($product->category_id);
        if (!$category) {
            return ['major' => null, 'middle' => null, 'minor' => null, 'middle_name' => '', 'minor_name' => '', 'final' => null];
        }

        $chain = [];
        while ($category) {
            array_unshift($chain, $category);
            if ((int) $category->parent_id === 0) {
                break;
            }
            $category = Category::find($category->parent_id);
        }

        return [
            'major' => $chain[0]->id ?? null,
            'middle' => $chain[1]->id ?? null,
            'minor' => $chain[2]->id ?? null,
            'middle_name' => $chain[1]->category_name ?? '',
            'minor_name' => $chain[2]->category_name ?? '',
            'final' => $product->category_id,
        ];
    }

    private function rootCategoryFor(Category $category): Category
    {
        while ((int) $category->parent_id !== 0) {
            $parent = Category::find($category->parent_id);
            if (!$parent) {
                break;
            }
            $category = $parent;
        }

        return $category;
    }

    private function productNoticeTypeForCategoryName(?string $categoryName): ?string
    {
        $normalizedName = preg_replace('/\s+/u', '', (string) $categoryName);
        foreach ($this->productNoticeMajorCategories() as $majorName => $type) {
            if ($normalizedName === preg_replace('/\s+/u', '', $majorName)) {
                return $type;
            }
        }

        foreach ($this->productNoticeTemplates() as $type => $template) {
            foreach ($template['keywords'] as $keyword) {
                $normalizedKeyword = preg_replace('/\s+/u', '', $keyword);
                if ($normalizedKeyword !== '' && mb_strpos($normalizedName, $normalizedKeyword) !== false) {
                    return $type;
                }
            }
        }

        return null;
    }

    private function productNoticeMajorCategories(): array
    {
        return [
            '식품/건강기능식품' => 'food_health',
            '의약외품/화장품' => 'cosmetics',
            '의류/패션' => 'fashion',
            '전자제품/전기용품' => 'electronics',
            '어린이제품/완구/유아용품' => 'children',
            '가구/인테리어' => 'furniture',
            '주류/담배' => 'liquor_tobacco',
            '자동차/오토바이 부품' => 'vehicle_parts',
            '기타 수입품' => 'imported',
        ];
    }

    private function productNoticeTemplates(): array
    {
        return [
            'food_health' => [
                'title' => '식품/건강기능식품',
                'keywords' => ['식품/건강기능식품', '식품', '건강기능식품'],
                'fields' => [
                    ['key' => 'product_name', 'label' => '제품명'],
                    ['key' => 'food_type', 'label' => '식품의 유형'],
                    ['key' => 'manufacturer', 'label' => '제조업소명'],
                    ['key' => 'location', 'label' => '소재지'],
                    ['key' => 'ingredients', 'label' => '원재료 명 및 함량'],
                    ['key' => 'expiration_date', 'label' => '유통기한 또는 품질유지기한'],
                    ['key' => 'manufactured_date', 'label' => '제조연월일'],
                    ['key' => 'content_amount', 'label' => '내용량 (중량, 부피 등)'],
                    ['key' => 'storage_method', 'label' => '보관방법'],
                    ['key' => 'intake_notice', 'label' => '섭취 방법 및 섭취 시 주의 사항'],
                    ['key' => 'functional_notice', 'label' => '건강기능식품 기능성 내용 및 질병 예방/치료 목적 아님 문구'],
                ],
            ],
            'cosmetics' => [
                'title' => '의약외품/화장품',
                'keywords' => ['의약외품/화장품', '의약외품', '화장품'],
                'fields' => [
                    ['key' => 'product_name', 'label' => '제품명'],
                    ['key' => 'capacity_weight', 'label' => '용량 및 중량'],
                    ['key' => 'ingredients', 'label' => '전 성분'],
                    ['key' => 'usage_notice', 'label' => '사용 방법 및 주의사항'],
                    ['key' => 'manufacturer_country', 'label' => '제조업체 및 제조국가'],
                    ['key' => 'expiration_date', 'label' => '유효기간(사용 기한)'],
                    ['key' => 'certification', 'label' => '기능성 화장품일 경우 식약처 인증 내용'],
                ],
            ],
            'fashion' => [
                'title' => '의류/패션',
                'keywords' => ['의류/패션', '의류', '패션'],
                'fields' => [
                    ['key' => 'product_name', 'label' => '제품명'],
                    ['key' => 'material', 'label' => '소재(섬유의 혼용률)'],
                    ['key' => 'manufacturer_country', 'label' => '제조업체명 및 제조국가'],
                    ['key' => 'size', 'label' => '사이즈'],
                    ['key' => 'washing_method', 'label' => '세탁 방법'],
                    ['key' => 'manufactured_date', 'label' => '제조연월'],
                ],
            ],
            'electronics' => [
                'title' => '전자제품/전기용품',
                'keywords' => ['전자제품/전기용품', '전자제품', '전기용품'],
                'fields' => [
                    ['key' => 'product_name', 'label' => '제품명'],
                    ['key' => 'model_name', 'label' => '모델명'],
                    ['key' => 'safety_certification', 'label' => '전기용품안전인증번호'],
                    ['key' => 'manufacturer_country', 'label' => '제조업체명 및 제조국가'],
                    ['key' => 'manufactured_date', 'label' => '제조연월'],
                    ['key' => 'rated_power', 'label' => '정격전압/소비전력'],
                    ['key' => 'kc_certification', 'label' => 'KC 인증마크 및 번호'],
                    ['key' => 'as_contact', 'label' => 'AS 연락처'],
                ],
            ],
            'children' => [
                'title' => '어린이제품/완구/유아용품',
                'keywords' => ['어린이제품/완구/유아용품', '어린이', '완구', '유아용품'],
                'fields' => [
                    ['key' => 'product_name', 'label' => '제품명'],
                    ['key' => 'age_range', 'label' => '사용 연령 또는 적정 연령'],
                    ['key' => 'safety_certification', 'label' => '안전 인증 마크 및 번호'],
                    ['key' => 'warning_notice', 'label' => '주의 문구'],
                    ['key' => 'manufacturer_country', 'label' => '제조업체명 및 제조국가'],
                ],
            ],
            'furniture' => [
                'title' => '가구/인테리어',
                'keywords' => ['가구/인테리어', '가구', '인테리어'],
                'fields' => [
                    ['key' => 'product_name', 'label' => '제품명'],
                    ['key' => 'size_components', 'label' => '크기 및 구성'],
                    ['key' => 'material', 'label' => '주요 소재'],
                    ['key' => 'manufacturer_country', 'label' => '제조업체명 및 제조국가'],
                    ['key' => 'color_finish', 'label' => '색상 및 마감'],
                    ['key' => 'assembly', 'label' => '조립 여부'],
                ],
            ],
            'liquor_tobacco' => [
                'title' => '주류/담배',
                'keywords' => ['주류/담배', '주류', '담배'],
                'fields' => [
                    ['key' => 'product_name', 'label' => '제품명'],
                    ['key' => 'alcohol_content', 'label' => '알코올 함량'],
                    ['key' => 'manufacturer_country', 'label' => '제조업체명 및 제조국가'],
                    ['key' => 'expiration_date', 'label' => '유통기한'],
                    ['key' => 'warning_notice', 'label' => '주의사항 및 경고 문구'],
                ],
            ],
            'vehicle_parts' => [
                'title' => '자동차/오토바이 부품',
                'keywords' => ['자동차/오토바이 부품', '자동차', '오토바이', '부품'],
                'fields' => [
                    ['key' => 'product_name', 'label' => '제품명'],
                    ['key' => 'model_compatibility', 'label' => '모델명 및 호환 정보'],
                    ['key' => 'manufacturer_country', 'label' => '제조업체명 및 제조국가'],
                    ['key' => 'warranty', 'label' => '품질 보증기간'],
                    ['key' => 'kc_certification', 'label' => 'KC 인증 정보'],
                ],
            ],
            'imported' => [
                'title' => '기타 수입품',
                'keywords' => ['기타 수입품', '수입품'],
                'fields' => [
                    ['key' => 'origin', 'label' => '원산지'],
                    ['key' => 'importer_contact', 'label' => '수입업체명 및 연락처'],
                    ['key' => 'safety_certification', 'label' => '안전 인증 마크 및 번호'],
                    ['key' => 'customs_clearance', 'label' => '통관 여부'],
                ],
            ],
        ];
    }

    private function validateAdditionalImageLimit(Request $request, ?Product $product = null): void
    {
        $legacyImages = $this->validUploadedFiles($request->file('images', []));
        $slotImages = $this->validUploadedFiles($request->file('slot_images', []));
        $replacementCount = 0;

        $existingAdditionalCount = 0;
        if ($product && $product->exists) {
            $query = $product->images();
            if (!empty($product->product_image)) {
                $query->where('image', '!=', $product->product_image);
            }
            $existingAdditionalCount = $query->count();

            $slotImageIds = $request->input('slot_image_ids', []);
            $replacementIds = [];
            foreach (array_keys($slotImages) as $slot) {
                $imageId = (int) ($slotImageIds[$slot] ?? 0);
                if ($imageId > 0) {
                    $replacementIds[] = $imageId;
                }
            }

            if ($replacementIds) {
                $replacementQuery = ProductsImage::where('product_id', $product->id)
                    ->whereIn('id', $replacementIds);
                if (!empty($product->product_image)) {
                    $replacementQuery->where('image', '!=', $product->product_image);
                }
                $replacementCount = $replacementQuery->count();
            }
        }

        $newAdditionalCount = count($legacyImages) + count($slotImages) - $replacementCount;
        if ($existingAdditionalCount + $newAdditionalCount > 20) {
            throw ValidationException::withMessages([
                'images' => '추가 이미지는 기존 등록 이미지를 포함해 최대 20개까지 등록할 수 있습니다.',
            ]);
        }
    }

    private function fillBaseProduct(Product $product, array $data, $admin): void
    {
        $product->section_id = $data['section_id'];
        $product->category_id = $data['category_id'];
        $product->brand_id = $data['brand_id'];
        $product->product_name = $data['product_name'];
        $product->product_color = strtolower($data['product_color'] ?: '#000000');
        $product->product_price = $data['product_price'];
        $product->product_discount = $data['product_discount'] ?? 0;
        $product->product_weight = $data['product_weight'] ?? 1;
        $product->description = $data['description'] ?? null;
        $product->status = $data['status'];
        $saleScope = $data['sale_scope'] ?? 'own';
        $product->is_public = $saleScope === 'public' ? 'Yes' : 'No';
        $product->is_partial = $saleScope === 'affiliate' ? 'Yes' : 'No';
        $product->partial_approved = 'Approved';
        $product->meta_title = $data['product_name'];
        $product->meta_keywords = $data['product_name'];
        $product->meta_description = Str::limit(strip_tags((string) ($data['description'] ?? $data['product_name'])), 150);

        if (Schema::hasColumn('products', 'sale_scope')) {
            $product->sale_scope = $saleScope;
        }
        if (Schema::hasColumn('products', 'reward_points')) {
            $product->reward_points = $data['reward_points'] ?? null;
        }
        if (Schema::hasColumn('products', 'tax_type')) {
            $product->tax_type = $data['tax_type'] ?? 'taxable';
        }
        if (Schema::hasColumn('products', 'price_constraint_enabled')) {
            $enabled = (int) ($data['price_constraint_enabled'] ?? 0) === 1;
            $profitShareType = $enabled ? ($data['profit_share_type'] ?? 'none') : null;
            $product->price_constraint_enabled = $enabled;
            $product->price_constraint_type = $enabled ? ($data['price_constraint_type'] ?? 'range') : null;
            $product->price_min = $enabled && ($data['price_constraint_type'] ?? null) === 'range' ? ($data['price_min'] ?? null) : null;
            $product->price_max = $enabled && ($data['price_constraint_type'] ?? null) === 'range' ? ($data['price_max'] ?? null) : null;
            $product->price_fixed = $enabled && ($data['price_constraint_type'] ?? null) === 'fixed' ? ($data['price_fixed'] ?? null) : null;
            $product->profit_share_type = $profitShareType;
            $product->profit_share_value = $enabled && $profitShareType !== 'none' ? ($data['profit_share_value'] ?? null) : null;
        }
        if (Schema::hasColumn('products', 'purchase_limit_enabled')) {
            $limitEnabled = (int) ($data['purchase_limit_enabled'] ?? 0) === 1;
            $product->purchase_limit_enabled = $limitEnabled;
            $product->purchase_min_qty = $limitEnabled ? ($data['purchase_min_qty'] ?? null) : null;
            $product->purchase_max_qty = $limitEnabled ? ($data['purchase_max_qty'] ?? null) : null;
        }
        if (Schema::hasColumn('products', 'stock_usage')) {
            $product->stock_usage = $data['stock_usage'] ?? 'unused';
        }
        if (Schema::hasColumn('products', 'detail_display_type')) {
            $detailType = $data['detail_display_type'] ?? 'unused';
            $product->detail_display_type = $detailType;
            $product->detail_text = $detailType === 'text' ? ($data['detail_text'] ?? null) : null;
        }
        if (Schema::hasColumn('products', 'order_manager_enabled')) {
            $managerEnabled = (int) ($data['order_manager_enabled'] ?? 0) === 1;
            $product->order_manager_enabled = $managerEnabled;
            $product->distributor_id = $managerEnabled ? ($data['distributor_id'] ?? null) : null;
        }
        if (Schema::hasColumn('products', 'shipping_policy_type')) {
            $shippingPolicyType = $data['shipping_policy_type'] ?? 'free_conditional';
            $product->shipping_policy_type = $shippingPolicyType;
            $product->shipping_policy_name = $data['shipping_policy_name'] ?? null;
            $product->shipping_payment_type = $data['shipping_payment_type'] ?? 'prepaid';
            $product->shipping_base_fee = $shippingPolicyType !== 'free' ? ($data['shipping_base_fee'] ?? null) : null;
            $product->shipping_free_threshold = $shippingPolicyType === 'free_conditional'
                ? ($data['shipping_free_threshold'] ?? null)
                : null;
        }
        if (Schema::hasColumn('products', 'cancel_refund_policy_id')) {
            $product->cancel_refund_policy_id = $data['cancel_refund_policy_id'] ?? null;
        }
        if (Schema::hasColumn('products', 'product_notice_type')) {
            $product->product_notice_type = $data['product_notice_type'] ?? null;
        }
        if (Schema::hasColumn('products', 'product_notice_items')) {
            $product->product_notice_items = $data['product_notice_items'] ?? null;
        }
    }

    private function productOptionsForView(Product $product): array
    {
        $attributes = $product->relationLoaded('attributes') ? $product->attributes : collect();

        if ($attributes->isEmpty()) {
            return [[
                'id' => null,
                'option_name' => '기본옵션',
                'option_type' => 'general',
                'option_value' => '',
                'sku' => '',
                'price' => $product->product_price ?: 0,
                'stock' => 0,
                'status' => 1,
            ]];
        }

        return $attributes->map(function ($attribute) {
            return [
                'id' => $attribute->id,
                'option_name' => $attribute->option_name ?: '기본옵션',
                'option_type' => $attribute->option_type ?: 'general',
                'option_value' => $attribute->size,
                'sku' => $attribute->sku,
                'price' => $attribute->price,
                'stock' => $attribute->stock,
                'status' => $attribute->status,
            ];
        })->values()->toArray();
    }

    private function orderManagersForView()
    {
        $admin = Auth::guard('admin')->user();
        $query = \App\Models\Distributor::where('status', 1);

        if (Schema::hasColumn('distributors', 'vendor_id')) {
            $query->where('vendor_id', (int) ($admin->vendor_id ?? 0));
        }

        return $query
            ->orderBy('name')
            ->get();
    }

    private function cancelRefundPoliciesForView(?int $vendorId)
    {
        if (!$vendorId) {
            return collect();
        }

        return \App\Models\ShopCancelRefundPolicy::where('vendor_id', $vendorId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function shippingPolicyOptions(): array
    {
        return [
            'free_conditional' => '무료배송 (조건식)',
            'free' => '무료배송',
            'paid' => '유료배송',
        ];
    }

    private function syncProductOptions(Product $product, Request $request): void
    {
        $optionValues = $request->input('option_values', []);
        $optionIds = $request->input('option_ids', []);
        $optionNames = $request->input('option_names', []);
        $optionTypes = $request->input('option_types', []);
        $optionSkus = $request->input('option_skus', []);
        $optionPrices = $request->input('option_prices', []);
        $optionStocks = $request->input('option_stocks', []);
        $optionStatuses = $request->input('option_statuses', []);

        $seenValues = [];
        $keptIds = [];

        foreach ($optionValues as $index => $rawValue) {
            $value = trim((string) $rawValue);
            if ($value === '') {
                continue;
            }

            $valueKey = mb_strtolower($value);
            if (isset($seenValues[$valueKey])) {
                throw ValidationException::withMessages(['option_values.' . $index => '같은 옵션값은 중복 등록할 수 없습니다.']);
            }
            $seenValues[$valueKey] = true;

            $optionId = (int) ($optionIds[$index] ?? 0);
            $attribute = null;
            if ($optionId > 0) {
                $attribute = ProductsAttribute::where('id', $optionId)
                    ->where('product_id', $product->id)
                    ->first();
            }
            if (!$attribute) {
                $attribute = new ProductsAttribute();
                $attribute->product_id = $product->id;
            }

            $attribute->option_name = trim((string) ($optionNames[$index] ?? '')) ?: '기본옵션';
            $attribute->option_type = in_array(($optionTypes[$index] ?? 'general'), ['text', 'general', 'price'], true)
                ? $optionTypes[$index]
                : 'general';
            $attribute->size = $value;
            $attribute->sku = trim((string) ($optionSkus[$index] ?? '')) ?: $this->generateOptionSku($product, $index + 1);
            $attribute->price = (float) ($optionPrices[$index] ?? $product->product_price ?? 0);
            $attribute->stock = (int) ($optionStocks[$index] ?? 0);
            $attribute->status = (int) ($optionStatuses[$index] ?? 1);
            $attribute->save();

            $keptIds[] = $attribute->id;
        }

        $deleteQuery = ProductsAttribute::where('product_id', $product->id);
        if ($keptIds) {
            $deleteQuery->whereNotIn('id', $keptIds);
        }
        $deleteQuery->delete();
    }

    private function generateOptionSku(Product $product, int $index): string
    {
        $base = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $product->product_code) ?: 'OPTION';
        $candidate = $base . '-OPT' . $index;
        $suffix = 2;

        while (ProductsAttribute::where('sku', $candidate)->where('product_id', '!=', $product->id)->exists()) {
            $candidate = $base . '-OPT' . $index . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function storeProductDetailAssets(Product $product, Request $request): void
    {
        if (!Schema::hasColumn('products', 'detail_display_type')) {
            return;
        }

        if ($product->detail_display_type !== 'image') {
            if ($product->detail_pc_image) {
                $this->deleteProductDetailImageFile($product->detail_pc_image);
            }
            if ($product->detail_mobile_image) {
                $this->deleteProductDetailImageFile($product->detail_mobile_image);
            }
            $product->detail_pc_image = null;
            $product->detail_mobile_image = null;
            $product->save();
            return;
        }

        $updated = false;
        if ($request->hasFile('detail_pc_image') && $request->file('detail_pc_image')->isValid()) {
            if ($product->detail_pc_image) {
                $this->deleteProductDetailImageFile($product->detail_pc_image);
            }
            $product->detail_pc_image = $this->saveProductDetailImageFile($request->file('detail_pc_image'), 'pc');
            $updated = true;
        }

        if ($request->hasFile('detail_mobile_image') && $request->file('detail_mobile_image')->isValid()) {
            if ($product->detail_mobile_image) {
                $this->deleteProductDetailImageFile($product->detail_mobile_image);
            }
            $product->detail_mobile_image = $this->saveProductDetailImageFile($request->file('detail_mobile_image'), 'mobile');
            $updated = true;
        }

        if ($updated) {
            $product->save();
        }
    }

    private function saveProductDetailImageFile($image, string $prefix): string
    {
        $dir = public_path('front/images/product_detail_images');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $extension = strtolower($image->getClientOriginalExtension() ?: 'jpg');
        $imageName = 'detail_' . $prefix . '_' . now()->format('YmdHis') . '_' . Str::lower(Str::random(8)) . '.' . $extension;
        $image->move($dir, $imageName);

        return $imageName;
    }

    private function deleteProductDetailImageFile(?string $imageName): void
    {
        if (!$imageName) {
            return;
        }

        $path = public_path('front/images/product_detail_images/' . $imageName);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function storeUploadedProductImages(Product $product, Request $request): void
    {
        if ($request->hasFile('product_image') && $request->file('product_image')->isValid()) {
            $imageName = $this->saveProductImageFile($request->file('product_image'), 'main');
            $product->product_image = $imageName;
            $product->save();
            $this->createProductImageRow($product, $imageName);
        }

        foreach ($this->validUploadedFiles($request->file('images', [])) as $image) {
            $imageName = $this->saveProductImageFile($image, 'sub');
            if (empty($product->product_image)) {
                $product->product_image = $imageName;
                $product->save();
            }
            $this->createProductImageRow($product, $imageName);
        }

        $slotImageIds = $request->input('slot_image_ids', []);
        foreach ($this->validUploadedFiles($request->file('slot_images', [])) as $slot => $image) {
            $imageName = $this->saveProductImageFile($image, 'sub');
            $replaceId = (int) ($slotImageIds[$slot] ?? 0);

            if ($this->replaceProductImageRow($product, $replaceId, $imageName)) {
                continue;
            }

            if (empty($product->product_image)) {
                $product->product_image = $imageName;
                $product->save();
            }
            $this->createProductImageRow($product, $imageName);
        }
    }

    private function validUploadedFiles($files): array
    {
        if ($files instanceof UploadedFile) {
            return $files->isValid() ? [$files] : [];
        }

        if (!is_array($files)) {
            return [];
        }

        return collect($files)
            ->filter(fn ($image) => $image instanceof UploadedFile && $image->isValid())
            ->all();
    }

    private function saveProductImageFile($image, string $prefix): string
    {
        foreach (['large', 'medium', 'small'] as $size) {
            $dir = public_path('front/images/product_images/' . $size);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $extension = strtolower($image->getClientOriginalExtension() ?: 'jpg');
        $imageName = $prefix . '_' . now()->format('YmdHis') . '_' . Str::lower(Str::random(8)) . '.' . $extension;

        Image::make($image)->resize(1000, 1000)->save(public_path('front/images/product_images/large/' . $imageName));
        Image::make($image)->resize(500, 500)->save(public_path('front/images/product_images/medium/' . $imageName));
        Image::make($image)->resize(250, 250)->save(public_path('front/images/product_images/small/' . $imageName));

        return $imageName;
    }

    private function createProductImageRow(Product $product, string $imageName): void
    {
        $exists = ProductsImage::where('product_id', $product->id)
            ->where('image', $imageName)
            ->exists();

        if ($exists) {
            return;
        }

        $productImage = new ProductsImage();
        $productImage->product_id = $product->id;
        $productImage->image = $imageName;
        $productImage->status = 1;
        $productImage->save();
    }

    private function replaceProductImageRow(Product $product, int $imageId, string $imageName): bool
    {
        if ($imageId <= 0) {
            return false;
        }

        $productImage = ProductsImage::where('id', $imageId)
            ->where('product_id', $product->id)
            ->first();

        if (!$productImage) {
            return false;
        }

        $oldImageName = $productImage->image;
        $productImage->image = $imageName;
        $productImage->status = 1;
        $productImage->save();

        if ($oldImageName === $product->product_image) {
            $product->product_image = $imageName;
            $product->save();
        }

        if ($oldImageName && $oldImageName !== $imageName) {
            $this->deleteProductImageFiles($oldImageName);
        }

        return true;
    }

    private function deleteProductImageFiles(?string $imageName): void
    {
        if (!$imageName) {
            return;
        }

        foreach (['large', 'medium', 'small'] as $size) {
            $path = public_path('front/images/product_images/' . $size . '/' . $imageName);
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function generateProductCode(int $vendorId): string
    {
        do {
            $code = 'OWN-' . $vendorId . '-' . now()->format('ymdHis') . '-' . strtoupper(Str::random(4));
        } while (Product::where('product_code', $code)->exists());

        return $code;
    }

    public function updateStopNotice(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return response()->json(['status' => false, 'message' => '로그인이 필요합니다.']);

        $request->validate([
            'stop_notice_at' => 'required|date',
            'stop_notice_reason' => 'nullable|string|max:1000',
        ]);

        $product = Product::where('id', $id)
            ->where('vendor_id', $admin->vendor_id)
            ->first();

        if (!$product) {
            return response()->json(['status' => false, 'message' => '상품을 찾을 수 없거나 권한이 없습니다.']);
        }

        $product->stop_notice_at = $request->input('stop_notice_at');
        $product->stop_notice_reason = $request->input('stop_notice_reason');
        $product->stop_notice_requested_at = now();
        $product->save();

        return response()->json(['status' => true, 'message' => '판매중지 예고가 저장되었습니다.']);
    }

    public function deleteBaseProduct(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $product = Product::findOrFail($id);

        // Verify ownership
        if ($product->vendor_id != $admin->vendor_id) {
            if ($request->ajax()) return response()->json(['status' => false, 'message' => '권한이 없습니다.']);
            return redirect()->route('channel.product_own')->with('error_message', '권한이 없습니다.');
        }

        $orderedCount = OrdersProduct::where('product_id', $product->id)->count();
        if ($orderedCount > 0) {
            $message = '주문 이력이 있는 상품은 삭제할 수 없습니다. 판매중지 또는 판매중지 예고를 사용해 주세요.';
            if ($request->ajax()) return response()->json(['status' => false, 'message' => $message]);
            return redirect()->route('channel.product_own')->with('error_message', $message);
        }

        \App\Models\ShopChannelProduct::where('product_id', $product->id)->delete();
        $product->delete();

        if (request()->ajax()) return response()->json(['status' => true, 'message' => '상품이 삭제되었습니다.']);
        return redirect()->route('channel.product_own')->with('success_message', '상품이 삭제되었습니다.');
    }

    public function copyBaseProduct(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $product = Product::findOrFail($id);

        // Verify ownership
        if ($product->vendor_id != $admin->vendor_id) {
            return response()->json(['status' => false, 'message' => '권한이 없습니다.']);
        }

        $newProduct = $product->replicate();
        $newProduct->product_name = $product->product_name . ' (복사본)';
        $newProduct->product_code = $product->product_code . '_copy_' . now()->format('His');
        while (Product::where('product_code', $newProduct->product_code)->exists()) {
            $newProduct->product_code = $product->product_code . '_copy_' . now()->format('His') . '_' . Str::upper(Str::random(3));
        }
        $newProduct->save();

        return response()->json([
            'status' => true,
            'message' => '상품이 복사되었습니다.',
            'product_id' => $newProduct->id,
            'product_code' => $newProduct->product_code,
        ]);
    }

    public function updateRequestStatus(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return response()->json(['status' => false, 'message' => '로그인이 필요합니다.']);

        $data = $request->all();
        $requestIds = $request->input('request_ids', $request->input('request_id'));
        if (!is_array($requestIds)) {
            $requestIds = [$requestIds];
        }
        $requestIds = array_values(array_filter($requestIds));

        $validator = Validator::make($data, [
            'request_id' => 'required_without:request_ids|exists:shop_channel_products,id',
            'request_ids' => 'required_without:request_id|array',
            'request_ids.*' => 'exists:shop_channel_products,id',
            'status' => 'required|in:1,2', // 1: 허용, 2: 거부 (0: 대기는 DB 기본값)
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        if (empty($requestIds)) {
            return response()->json(['status' => false, 'message' => '선택된 판매요청이 없습니다.']);
        }

        $requests = \App\Models\ShopChannelProduct::with('product')
            ->whereIn('id', $requestIds)
            ->get();

        foreach ($requests as $shopProduct) {
            if (!$shopProduct->product || $shopProduct->product->vendor_id != $admin->vendor_id) {
                return response()->json(['status' => false, 'message' => '권한이 없는 판매요청이 포함되어 있습니다.']);
            }
        }

        foreach ($requests as $shopProduct) {
            $shopProduct->status = $data['status'];
            $shopProduct->approval_status = $data['status'] == 1 ? 'approved' : 'rejected';
            $shopProduct->reviewed_at = now();
            $shopProduct->reviewed_by = $admin->id;
            $shopProduct->save();
        }

        $actionName = $data['status'] == 1 ? '허용' : '거부';
        if (count($requests) === 1) {
            return response()->json(['status' => true, 'message' => '판매 요청이 성공적으로 ' . $actionName . ' 처리되었습니다.']);
        }

        return response()->json(['status' => true, 'message' => count($requests) . '건의 판매 요청이 성공적으로 ' . $actionName . ' 처리되었습니다.']);
    }
}
