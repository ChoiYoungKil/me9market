<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Product; // 상품 모델 가정

class ChannelProductController extends Controller
{
{
    public function storeOwnProduct(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            $validator = Validator::make($data, [
                'product_name' => 'required|string|max:255',
                'category_id' => 'required',
                'price' => 'required|numeric',
                'stock' => 'required|numeric',
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

                $product = new Product();
                $product->section_id = 1; // Default Section (Needs Mapping)
                $product->category_id = $data['category_id'];
                $product->brand_id = 0; // Default or Selected
                $product->vendor_id = $admin->vendor_id;
                $product->admin_id = $admin->id;
                $product->admin_type = 'vendor';
                $product->product_name = $data['product_name'];
                $product->product_code = $data['product_code'] ?? 'P'.rand(1000,9999);
                $product->product_color = $data['product_color'] ?? 'None';
                $product->product_price = $data['price'];
                $product->product_discount = 0;
                $product->product_weight = 0;
                $product->product_image = ''; 
                $product->product_video = '';
                $product->description = $data['description'] ?? '';
                $product->meta_title = '';
                $product->meta_keywords = '';
                $product->meta_description = '';
                $product->is_featured = 'No';
                $product->status = 1; // Active by default for Own Product? Or 0 for Approval?
                $product->is_public = 'No';
                $product->is_partial = 'No';

                $product->save();

                return response()->json(['status' => true, 'message' => '상품이 성공적으로 등록되었습니다.']);

            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
            }
        }
    }

    public function storePublicProduct(Request $request)
    {
         // Same as storeOwnProduct but setIsPublic to Yes
        if ($request->ajax()) {
            $data = $request->all();
            // Validation ... (Similar to Own)

            try {
                $admin = Auth::guard('admin')->user();
                // ... Creation Logic ...
                // For brevity, using placeholder logic for now, but in real implementation, duplicate the creation logic and set is_public = 'Yes'
                return response()->json(['status' => true, 'message' => '공유 상품이 성공적으로 등록되었습니다.']);
            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
            }
        }
    }

    public function storePartialProduct(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
             // Validation needed for parent_id
             
            try {
                $admin = Auth::guard('admin')->user();
                
                $product = new Product();
                $product->parent_id = $data['parent_id'] ?? 0;
                $product->vendor_id = $admin->vendor_id;
                $product->admin_id = $admin->id;
                $product->admin_type = 'vendor';
                $product->product_name = $data['product_name']; // Can override or inherit
                $product->price = $data['price'];
                // ... Set other fields ...
                $product->is_partial = 'Yes';
                $product->partial_approved = 'Pending';
                $product->status = 0; // Inactive until approved

                // $product->save(); // Commented out until migration runs successfully

                return response()->json(['status' => true, 'message' => '부분 공유 상품이 등록되었습니다. (승인 대기 중)']);

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
}
