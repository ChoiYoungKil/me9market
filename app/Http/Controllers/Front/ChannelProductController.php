<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Product; // 상품 모델 가정

class ChannelProductController extends Controller
{
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

                // Create Mapping
                \App\Models\ShopChannelProduct::create([
                    'shop_channel_id' => $shop->id,
                    'product_id' => $product->id,
                    'product_type' => 'own',
                    'status' => 1, // 판매중 상태로 등록 (필요시 0으로)
                    'constraint_type' => 'none', // 추후 제약조건 폼 필드 연동 필요
                    'stock' => $product->stock,
                    'product_price' => $product->product_price,
                    'selling_price' => $data['selling_price'],
                    'profit' => max(0, $data['selling_price'] - $product->product_price), // 간단 이익 계산
                ]);

                return response()->json(['status' => true, 'message' => '상품이 성공적으로 채널에 추가되었습니다.']);

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

                \App\Models\ShopChannelProduct::create([
                    'shop_channel_id' => $shop->id,
                    'product_id' => $product->id,
                    'product_type' => 'public',
                    'status' => 1,
                    'constraint_type' => 'none',
                    'stock' => $product->stock,
                    'product_price' => $product->product_price,
                    'selling_price' => $data['selling_price'],
                    'profit' => max(0, $data['selling_price'] - $product->product_price),
                ]);

                return response()->json(['status' => true, 'message' => '공유 상품이 성공적으로 추가되었습니다.']);
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

                $product = Product::where('id', $data['product_id'])
                    ->first();
                if (!$product) {
                    return response()->json(['status' => false, 'message' => '상품을 찾을 수 없습니다.']);
                }

                $exists = \App\Models\ShopChannelProduct::where('shop_channel_id', $shop->id)
                    ->where('product_id', $product->id)
                    ->exists();

                if ($exists) {
                    return response()->json(['status' => false, 'message' => '이미 추가되었거나 권한 요청이 접수된 상품입니다.']);
                }

                // 부분 공유 상품은 승인 대기로 등록 (status = 0, partial_approved 처리 등)
                \App\Models\ShopChannelProduct::create([
                    'shop_channel_id' => $shop->id,
                    'product_id' => $product->id,
                    'product_type' => 'partial',
                    'status' => 0, // 판매 승인 대기
                    'constraint_type' => 'none',
                    'stock' => $product->stock,
                    'product_price' => $product->product_price,
                    'selling_price' => $data['selling_price'],
                    'profit' => max(0, $data['selling_price'] - $product->product_price),
                ]);

                return response()->json(['status' => true, 'message' => '판매 권한 요청이 성공적으로 접수되었습니다. (승인 대기)']);

            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
            }
        }
    }


    public function requestPartialProduct(Request $request)
    {
         // This might just be an email or a notification to the Original Seller
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

        $shopProduct->selling_price = $request->input('selling_price');
        $shopProduct->stock = $request->input('stock');
        $shopProduct->purchase_limit = $request->input('purchase_limit');
        $shopProduct->status = $request->input('status');
        
        // Calculate profit consider settlement rate (Commission)
        // Profit = Selling Price - (Selling Price * Rate/100) - CostPrice
        $rate = $shop->settlement_rate ?? 10;
        $commission = $shopProduct->selling_price * ($rate / 100);
        $shopProduct->profit = $shopProduct->selling_price - $commission - $shopProduct->product_price;
        
        $shopProduct->save();

        return redirect()->route('channel.shop_product01', ['shop_id' => $shop->id])
            ->with('success_message', '상품 정보가 성공적으로 수정되었습니다.');
    }
    public function getBaseProductDetail($id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return response()->json(['status' => false, 'message' => '로그인이 필요합니다.']);

        $product = Product::with(['category', 'images', 'brand', 'parentCategory'])->find($id);
        if (!$product) return response()->json(['status' => false, 'message' => '상품을 찾을 수 없습니다.']);

        return response()->json([
            'status' => true,
            'product' => $product,
            'category_path' => ($product->parentCategory ? $product->parentCategory->category_name . ' > ' : '') . ($product->category->category_name ?? '')
        ]);
    }

    public function editBaseProduct($id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $product = Product::with(['category', 'images', 'brand'])->findOrFail($id);

        // Verify ownership
        if ($product->vendor_id != $admin->vendor_id) {
            return redirect()->route('channel.product_own')->with('error_message', '권한이 없습니다.');
        }

        $categories = \App\Models\Category::with('subCategories')->where('parent_id', 0)->get();
        $sections = \App\Models\Section::where('status', 1)->get();

        return view('channel.sub02.product_base_edit', [
            'dep1_id' => '02',
            'product' => $product,
            'categories' => $categories,
            'sections' => $sections
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

        $request->validate([
            'product_name' => 'required',
            'product_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:0,1',
            'is_public' => 'required|in:0,1',
            'is_partial' => 'required|in:0,1',
        ]);

        $product->update($request->all());

        return redirect()->route('channel.product_own')->with('success_message', '상품 정보가 성공적으로 수정되었습니다.');
    }

    public function deleteBaseProduct($id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return redirect()->route('channel.login');

        $product = Product::findOrFail($id);

        // Verify ownership
        if ($product->vendor_id != $admin->vendor_id) {
            if ($request->ajax()) return response()->json(['status' => false, 'message' => '권한이 없습니다.']);
            return redirect()->route('channel.product_own')->with('error_message', '권한이 없습니다.');
        }

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
        $newProduct->product_code = $product->product_code . '_copy_' . time();
        $newProduct->save();

        return response()->json(['status' => true, 'message' => '상품이 복사되었습니다.']);
    }

    public function updateRequestStatus(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return response()->json(['status' => false, 'message' => '로그인이 필요합니다.']);

        $data = $request->all();
        $validator = Validator::make($data, [
            'request_id' => 'required|exists:shop_channel_products,id',
            'status' => 'required|in:1,2', // 1: 허용, 2: 거부 (0: 대기는 DB 기본값)
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $shopProduct = \App\Models\ShopChannelProduct::find($data['request_id']);

        // Verify the authenticated vendor OWNS the base product
        $product = Product::find($shopProduct->product_id);
        if ($product->vendor_id != $admin->vendor_id) {
            return response()->json(['status' => false, 'message' => '권한이 없습니다 (본인의 상품에 대한 요청이 아닙니다).']);
        }

        $shopProduct->status = $data['status'];
        $shopProduct->save();

        $actionName = $data['status'] == 1 ? '허용' : '거부';
        return response()->json(['status' => true, 'message' => '판매 요청이 성공적으로 ' . $actionName . ' 처리되었습니다.']);
    }
}
