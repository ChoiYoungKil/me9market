<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductsAttribute;
use App\Models\ProductsFilter;
use App\Models\ProductsImage;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Intervention\Image\Facades\Image;

class ProductsController extends Controller
{
    public function products(Request $request)
    {
        Session::put('page','products');
        $filters=$request->validate(['keyword'=>'nullable|string|max:100','search_type'=>'nullable|in:all,product_name,product_code','status'=>'nullable|in:0,1','from'=>'nullable|date_format:Y-m-d','to'=>['nullable','date_format:Y-m-d',\Illuminate\Validation\Rule::when($request->filled('from'),'after_or_equal:from')],'per_page'=>'nullable|in:10,20,50,100','view'=>'nullable|in:table,gallery','export'=>'nullable|in:1']);
        $query=$this->productQuery()->with(['category','section']);
        if($request->filled('keyword'))$query->where(function($q)use($filters){foreach(['product_name','product_code'] as $field)if(($filters['search_type']??'all')==='all'||$filters['search_type']===$field)$q->orWhere($field,'like','%'.$filters['keyword'].'%');});
        if($request->filled('status'))$query->where('status',$filters['status']);
        if($request->filled('from'))$query->where('created_at','>=',$filters['from'].' 00:00:00');
        if($request->filled('to'))$query->where('created_at','<',\Carbon\Carbon::parse($filters['to'])->addDay()->toDateString());
        $query->orderByDesc('id');
        if($request->boolean('export'))return app(\App\Services\AdminListExport::class)->download($query,'상품목록',['상품번호'=>'id','상품명'=>'product_name','상품코드'=>'product_code','분류'=>'category.category_name','회원사번호'=>'vendor_id','판매가격'=>'product_price','상태'=>fn($p)=>$p->status?'활성':'비활성']);
        $products=$query->paginate((int)($filters['per_page']??20))->withQueryString();
        return view('admin.products.products',compact('products','filters'));
    }

    public function policies(Request $request, int $id)
    {
        abort_unless(in_array(Auth::guard('admin')->user()?->type,['admin','superadmin'],true),403);
        Session::put('page','products');
        $product=Product::findOrFail($id);
        if($request->isMethod('post')) {
            $data=$request->validate([
                'shipping_policy_id'=>['present','nullable','integer',\Illuminate\Validation\Rule::exists('channel_delivery_charges','id')->where('vendor_id',$product->vendor_id)->where('status',1)],
                'cancel_refund_policy_id'=>['present','nullable','integer',\Illuminate\Validation\Rule::exists('shop_cancel_refund_policies','id')->where('vendor_id',$product->vendor_id)->where('status','active')],
            ]);
            \Illuminate\Support\Facades\DB::transaction(function()use($product,$data){
                $locked=Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
                if(!empty($data['shipping_policy_id'])) {
                    $policy=\App\Models\ChannelDeliveryCharge::whereKey($data['shipping_policy_id'])->where('vendor_id',$locked->vendor_id)->where('status',1)->lockForUpdate()->firstOrFail();
                    $locked->shipping_policy_id=$policy->id;$locked->shipping_policy_name=$policy->name;
                    $locked->shipping_policy_type=match($policy->shipping_type){'free'=>'free','conditional'=>'free_conditional',default=>'paid'};
                    $locked->shipping_payment_type=$policy->payment_type==='cod'?'collect':'prepaid';
                    $locked->shipping_base_fee=$policy->shipping_type==='fixed'?$policy->fixed_fee:$policy->base_fee;$locked->shipping_free_threshold=$policy->free_order_amount;
                } else { $locked->shipping_policy_id=null;$locked->shipping_policy_name=null; }
                $locked->cancel_refund_policy_id=$data['cancel_refund_policy_id']??null;
                $locked->save();
            });
            return redirect()->route('admin.products.policies',$product->id)->with('success_message','상품에 적용할 배송비·취소/환불 정책이 저장되었습니다.');
        }
        $deliveryPolicies=\App\Models\ChannelDeliveryCharge::where('vendor_id',$product->vendor_id)->where(fn($q)=>$q->where('status',1)->orWhere('id',$product->shipping_policy_id))->orderBy('name')->get();
        $refundPolicies=\App\Models\ShopCancelRefundPolicy::where('vendor_id',$product->vendor_id)->where(fn($q)=>$q->where('status','active')->orWhere('id',$product->cancel_refund_policy_id))->orderBy('name')->get();
        $listing=\App\Models\ShopChannelProduct::with('shopChannel')->where('product_id',$product->id)->where('status',1)->where('approval_status','approved')->limit(100)->get()->first(fn($listing)=>$listing->shopChannel && app(\App\Services\ShopChannelRuntime::class)->isChannelAvailable($listing->shopChannel));
        $previewUrl=$listing?url('shop-channel/enter/'.rawurlencode($listing->shopChannel->channel_code).'?product='.$listing->id):null;
        return view('admin.products.policies',compact('product','deliveryPolicies','refundPolicies','previewUrl'));
    }

    public function updateProductStatus(Request $request) // AJAX를 사용하여 상품 상태 업데이트
    {
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if (in_array($data['status'], ['Active','활성'],true)) { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }

            $product = $this->productQuery()->findOrFail($data['product_id']);
            $product->update(['status' => $status]);
            // echo '<pre>', var_dump($data), '</pre>';

            return response()->json([
                'status' => $status,
                'product_id' => $data['product_id'],
            ]);
        }
    }

    public function deleteProduct($id)
    {
        $this->productQuery()->findOrFail($id)->delete();

        $message = '상품이 성공적으로 삭제되었습니다!';

        return redirect()->back()->with('success_message', $message);
    }

    public function addEditProduct(Request $request, $id = null) // 상품 추가 또는 수정
    {// 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'products');

        if ($id == '') { // $id가 없으면 상품 추가
            $title = '상품 추가';
            $product = new Product;
            // dd($product);
            $message = '상품이 성공적으로 추가되었습니다!';
        } else { // $id가 있으면 상품 수정
            $title = '상품 수정';
            $product = $this->productQuery()->findOrFail($id);
            // dd($product);
            $message = '상품이 성공적으로 업데이트되었습니다!';
        }

        if ($request->isMethod('post')) { // 폼 제출인 경우 (추가 또는 수정 공용)
            $data = $request->all();
            // dd($data);

            // 라라벨 유효성 검사
            $rules = [
                'category_id' => 'required',
                'product_name' => 'required',
                'product_code' => 'required|regex:/^\w+$/',
                'product_price' => 'required|numeric',
                'product_color' => 'required|regex:/^[\pL\s\-]+$/u',
                'product_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
                'product_video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:102400',
            ];

            $customMessages = [
                'category_id.required' => '카테고리를 선택해 주세요',
                'product_name.required' => '상품명을 입력해 주세요',
                'product_name.regex' => '유효한 상품명을 입력해 주세요',
                'product_code.required' => '상품 코드를 입력해 주세요',
                'product_code.regex' => '유효한 상품 코드를 입력해 주세요',
                'product_price.required' => '상품 가격을 입력해 주세요',
                'product_price.numeric' => '유효한 상품 가격을 입력해 주세요',
                'product_color.required' => '상품 색상을 입력해 주세요',
                'product_color.regex' => '유효한 상품 색상을 입력해 주세요',

            ];

            $this->validate($request, $rules, $customMessages);

            // 리사이징 후 상품 이미지 업로드
            // 참고: 상품 이미지는 3가지 크기로 관리됩니다: large(1000x1000), medium(500x500), small(250x250)
            if ($request->hasFile('product_image')) {
                $image_tmp = $request->file('product_image');
                if ($image_tmp->isValid()) {
                    // 이미지 확장자 가져오기
                    $extension = $image_tmp->getClientOriginalExtension();

                    // 이미지 중복 방지를 위한 랜덤 이름 생성
                    $imageName = (string) \Illuminate\Support\Str::uuid().'.'.$extension;

                    // 'public' 폴더 내 업로드된 이미지 경로 할당
                    // 이미지 크기에 따라 small, medium, large 세 개의 폴더를 사용합니다.
                    $largeImagePath = public_path('front/images/product_images/large/'.$imageName);
                    $mediumImagePath = public_path('front/images/product_images/medium/'.$imageName);
                    $smallImagePath = public_path('front/images/product_images/small/'.$imageName);

                    // 'Intervention' 패키지를 사용하여 이미지 업로드 및 'public' 폴더 내 세 개의 경로(폴더)에 저장
                    Image::make($image_tmp)->resize(1000, 1000)->save($largeImagePath);  // 'large'  이미지 크기로 리사이즈 후 'large'  폴더에 저장
                    Image::make($image_tmp)->resize(500, 500)->save($mediumImagePath); // 'medium' 이미지 크기로 리사이즈 후 'medium' 폴더에 저장
                    Image::make($image_tmp)->resize(250, 250)->save($smallImagePath);  // 'small'  이미지 크기로 리사이즈 후 'small'  폴더에 저장

                    // 데이터베이스에 이미지 이름 저장
                    $product->product_image = $imageName;
                }
            }

            // 상품 동영상 업로드
            if ($request->hasFile('product_video')) {
                $video_tmp = $request->file('product_video');

                if ($video_tmp->isValid()) { // 성공적인 업로드 검증: https://laravel.com/docs/9.x/requests#validating-successful-uploads
                    // 동영상 업로드
                    $extension = $video_tmp->getClientOriginalExtension();

                    // 업로드된 동영상의 새로운 랜덤 이름 생성 (파일명 중복으로 인한 덮어쓰기 방지)
                    $videoName = (string) \Illuminate\Support\Str::uuid().'.'.$extension;

                    // 'public' 폴더 내 업로드된 동영상 경로 할당
                    $videoPath = public_path('front/videos/product_videos');

                    // 임시 경로(웹 서버 할당)에서 'public' 폴더 내 지정된 경로로 동영상 이동    // 파일 복사 및 이동: https://laravel.com/docs/9.x/filesystem#copying-moving-files
                    $video_tmp->move($videoPath, $videoName);

                    // 데이터베이스 테이블에 동영상 이름 저장
                    $product->product_video = $videoName;
                }
            }

            // 새 상품 추가 및 기존 상품 업데이트 정보를 `products` 테이블에 저장
            $categoryDetails = Category::find($data['category_id']);
            // dd($categoryDetails);

            $product->section_id = $categoryDetails['section_id'];
            $product->category_id = $data['category_id'];
            $product->brand_id = $data['brand_id'];
            $product->group_code = $data['group_code']; // 상품 색상 관리를 위한 그룹 코드

            // 상품에 필터 정보 저장
            $productFilters = ProductsFilter::productFilters();
            foreach ($productFilters as $filter) {
                // dd($filter);

                // 카테고리별 사용 가능한 필터 확인 후 저장
                $filterAvailable = ProductsFilter::filterAvailable($filter['id'], $data['category_id']);
                if ($filterAvailable == 'Yes') {
                    if (isset($filter['filter_column']) && $data[$filter['filter_column']]) {
                        // `products` 테이블에 필터 정보 저장
                        $product->{$filter['filter_column']} = $data[$filter['filter_column']];
                    }
                }
            }

            if ($id == '') { // 관리자나 입점업체가 새 상품을 추가한 경우 정보를 저장합니다. 수정 시에는 기존 값을 유지합니다.
                $adminType = Auth::guard('admin')->user()->type;
                $vendor_id = Auth::guard('admin')->user()->vendor_id;
                $admin_id = Auth::guard('admin')->user()->id;

                $product->admin_type = $adminType;
                $product->admin_id = $admin_id;

                if ($adminType == 'vendor') {
                    $product->vendor_id = $vendor_id;
                } else {
                    $product->vendor_id = 0;
                }
            }

            if (empty($data['product_discount'])) {
                $data['product_discount'] = 0;
            }

            if (empty($data['product_weight'])) {
                $data['product_weight'] = 0;
            }

            $product->product_name = $data['product_name'];
            $product->product_code = $data['product_code'];
            $product->product_color = $data['product_color'];
            $product->product_price = $data['product_price'];
            $product->product_discount = $data['product_discount'];
            $product->product_weight = $data['product_weight'];
            $product->description = $data['description'];
            $product->meta_title = $data['meta_title'];
            $product->meta_description = $data['meta_description'];
            $product->meta_keywords = $data['meta_keywords'];

            if (! empty($data['is_featured'])) {
                // dd($data);
                $product->is_featured = $data['is_featured'];
            } else {
                // dd($data);
                $product->is_featured = 'No';
            }

            if (! empty($data['is_bestseller'])) {
                // dd($data);
                $product->is_bestseller = $data['is_bestseller'];
            } else {
                // dd($data);
                $product->is_bestseller = 'No';
            }

            $product->status = 1;

            $product->save(); // 모든 데이터를 데이터베이스에 저장

            return redirect('admin/products')->with('success_message', $message);
        }

        // 모든 섹션, 카테고리 및 서브카테고리 가져오기
        $categories = Section::with('categories')->get()->toArray(); // with('categories')는 Section.php 모델의 관계 메소드 이름입니다.
        // dd($categories);

        // 모든 브랜드 가져오기
        $brands = Brand::where('status', 1)->get()->toArray();
        // dd($brands);

        // return view('admin.products.add_edit_product')->with(compact('title', 'product'));
        return view('admin.products.add_edit_product')->with(compact('title', 'product', 'categories', 'brands'));
    }

    public function deleteProductImage($id) // admin/js/custom.js의 AJAX 호출 - 서버와 DB에서 상품 이미지 삭제
    {// 데이터베이스에 저장된 상품 이미지 레코드 가져오기
        $productImage = $this->productQuery()->select('product_image')->findOrFail($id);
        // dd($productImage);

        // 서버 상의 상품 이미지 3단계 경로 (small, medium, large 폴더)
        $small_image_path = public_path('front/images/product_images/small/');
        $medium_image_path = public_path('front/images/product_images/medium/');
        $large_image_path = public_path('front/images/product_images/large/');

        // 서버에서 실제 상품 이미지 삭제 (3개 폴더 모두)
        // 첫 번째: 'small' 폴더에서 삭제
        if (file_exists($small_image_path.$productImage->product_image)) {
            unlink($small_image_path.$productImage->product_image);
        }

        // 두 번째: 'medium' 폴더에서 삭제
        if (file_exists($medium_image_path.$productImage->product_image)) {
            unlink($medium_image_path.$productImage->product_image);
        }

        // 세 번째: 'large' 폴더에서 삭제
        if (file_exists($large_image_path.$productImage->product_image)) {
            unlink($large_image_path.$productImage->product_image);
        }

        // products 테이블에서 상품 이미지 레코드 삭제 (컬럼 값을 빈 문자열로 업데이트)
        $this->productQuery()->whereKey($id)->update(['product_image' => '']);

        $message = '상품 이미지가 성공적으로 삭제되었습니다!';

        return redirect()->back()->with('success_message', $message);
    }

    public function deleteProductVideo($id) // admin/js/custom.js의 AJAX 호출 - 서버와 DB에서 상품 동영상 삭제
    {// 데이터베이스에 저장된 상품 동영상 레코드 가져오기
        $productVideo = $this->productQuery()->select('product_video')->findOrFail($id);
        // dd($productVideo);

        // 서버 상의 상품 동영상 경로
        $product_video_path = public_path('front/videos/product_videos/');

        // 서버에서 상품 동영상 삭제
        if (file_exists($product_video_path.$productVideo->product_video)) {
            unlink($product_video_path.$productVideo->product_video);
        }

        // products 테이블에서 상품 동영상 레코드 삭제 (컬럼 값을 빈 문자열로 업데이트)
        $this->productQuery()->whereKey($id)->update(['product_video' => '']);

        $message = '상품 동영상이 성공적으로 삭제되었습니다!';

        return redirect()->back()->with('success_message', $message);
    }

    public function addAttributes(Request $request, $id) // 속성 추가/수정 함수
    {
        Session::put('page', 'products');

        $product = $this->productQuery()
            ->select('id', 'product_name', 'product_code', 'product_color', 'product_price', 'product_image')
            ->with('attributes')
            ->findOrFail($id); // with('attributes')는 Product.php 모델의 관계 메소드 이름입니다.

        if ($request->isMethod('post')) { // 폼이 제출되었을 때
            $data = $request->validate([
                'size' => ['required', 'array', 'min:1', 'max:50'],
                'size.*' => ['required', 'string', 'max:255', 'distinct:ignore_case'],
                'sku' => ['required', 'array', 'min:1', 'max:50'],
                'sku.*' => ['required', 'string', 'max:255', 'distinct:ignore_case'],
                'price' => ['required', 'array', 'min:1', 'max:50'],
                'price.*' => ['required', 'numeric', 'min:0'],
                'stock' => ['required', 'array', 'min:1', 'max:50'],
                'stock.*' => ['required', 'integer', 'min:0', 'max:2147483647'],
            ], $this->optionValidationMessages(), $this->optionValidationAttributes());
            $this->validateOptionRowKeys($data, ['size', 'sku', 'price', 'stock']);

            \Illuminate\Support\Facades\DB::transaction(function () use ($data, $id) {
                $lockedProduct = $this->productQuery()->whereKey($id)->lockForUpdate()->firstOrFail();
                // Check the entire batch before writing so duplicates cannot leave partial rows.
                if (ProductsAttribute::whereIn('sku', $data['sku'])->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'sku' => '이미 등록된 옵션코드가 있습니다. 다른 옵션코드를 입력해 주세요.',
                    ]);
                }
                if (ProductsAttribute::where('product_id', $id)->whereIn('size', $data['size'])->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'size' => '이 상품에 이미 등록된 옵션명입니다. 다른 옵션명을 입력해 주세요.',
                    ]);
                }
                foreach ($data['size'] as $key => $size) {
                    $adjustment = (float) $data['price'][$key] - (float) $lockedProduct->product_price;
                    ProductsAttribute::create([
                        'product_id' => $id, 'size' => $size, 'sku' => $data['sku'][$key],
                        'price' => $data['price'][$key], 'stock' => $data['stock'][$key], 'status' => 1,
                        'option_name' => '기본옵션', 'option_type' => $adjustment == 0 ? 'general' : 'price',
                        'price_adjustment' => $adjustment,
                    ]);
                }
            });

            return redirect('admin/add-edit-attributes/'.$id)->with('success_message', '상품 옵션이 등록되었습니다.');
        }

        return view('admin.attributes.add_edit_attributes')->with(compact('product'));
    }

    public function updateAttributeStatus(Request $request) // add_edit_attributes.blade.php에서 AJAX를 사용한 속성 상태 업데이트
    {
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if (in_array($data['status'], ['Active','활성'],true)) { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }

            $attribute = $this->attributeQuery()->findOrFail($data['attribute_id']);
            $attribute->update(['status' => $status]); // $data['attribute_id']는 $.ajax() 메소드 내 'data' 객체에서 가져옵니다.

            return response()->json([
                'status' => $status,
                'attribute_id' => $data['attribute_id'],
            ]);
        }
    }

    public function editAttributes(Request $request)
    {
        Session::put('page', 'products');
        $productId = (int) $request->route('id');
        $this->productQuery()->findOrFail($productId);
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'attributeId' => ['required', 'array', 'min:1'],
                'attributeId.*' => ['required', 'integer', 'distinct'],
                'price' => ['required', 'array', 'min:1'],
                'price.*' => ['required', 'numeric', 'min:0'],
                'stock' => ['required', 'array', 'min:1'],
                'stock.*' => ['required', 'integer', 'min:0', 'max:2147483647'],
            ], $this->optionValidationMessages(), $this->optionValidationAttributes());
            $this->validateOptionRowKeys($data, ['attributeId', 'price', 'stock']);

            \Illuminate\Support\Facades\DB::transaction(function () use ($data, $productId) {
                $product = $this->productQuery()->whereKey($productId)->lockForUpdate()->firstOrFail();
                $attributes = $this->attributeQuery()->where('product_id', $productId)
                    ->whereIn('id', $data['attributeId'])->lockForUpdate()->get()->keyBy('id');
                abort_unless($attributes->count() === count($data['attributeId']), 404);
                foreach ($data['attributeId'] as $key => $id) {
                    $attribute = $attributes->get((int) $id);
                    if ($attribute->option_type === 'text' && (float) $data['price'][$key] !== (float) $attribute->price) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'price' => '직접입력형 옵션의 가격은 이 화면에서 변경할 수 없습니다.',
                        ]);
                    }
                    $values = [
                        'price' => $data['price'][$key], 'stock' => $data['stock'][$key],
                    ];
                    if ($attribute->option_type !== 'text') {
                        $adjustment = (float) $data['price'][$key] - (float) $product->product_price;
                        $values += ['option_type' => $adjustment == 0 ? 'general' : 'price', 'price_adjustment' => $adjustment];
                    }
                    $attribute->update($values);
                }
            });

            return redirect('admin/add-edit-attributes/'.$productId)->with('success_message', '옵션 판매가와 재고가 저장되었습니다.');
        }
    }

    private function validateOptionRowKeys(array $data, array $fields): void
    {
        $keys = array_keys($data[$fields[0]]);
        foreach ($fields as $field) {
            if (array_keys($data[$field]) !== $keys) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $field => '옵션별 입력 항목 수가 일치하지 않습니다. 모든 항목을 확인해 주세요.',
                ]);
            }
        }
    }

    private function optionValidationMessages(): array
    {
        return [
            'required' => ':attribute 항목을 입력해 주세요.',
            'array' => ':attribute 입력 형식을 확인해 주세요.',
            'string' => ':attribute 항목은 문자로 입력해 주세요.',
            'numeric' => ':attribute 항목은 숫자로 입력해 주세요.',
            'integer' => ':attribute 항목은 정수로 입력해 주세요.',
            'distinct' => ':attribute 항목이 중복되었습니다.',
            'size.max' => '한 번에 최대 50개 옵션을 등록할 수 있습니다.',
            'sku.max' => '한 번에 최대 50개 옵션을 등록할 수 있습니다.',
            'price.max' => '한 번에 최대 50개 옵션을 등록할 수 있습니다.',
            'stock.max' => '한 번에 최대 50개 옵션을 등록할 수 있습니다.',
            'size.*.max' => '옵션명은 255자 이내로 입력해 주세요.',
            'sku.*.max' => '옵션코드는 255자 이내로 입력해 주세요.',
            'stock.*.max' => '재고는 2,147,483,647개 이하로 입력해 주세요.',
            'price.*.min' => '옵션 판매가는 0원 이상으로 입력해 주세요.',
            'stock.*.min' => '재고는 0개 이상으로 입력해 주세요.',
            'min' => ':attribute 항목을 한 개 이상 입력해 주세요.',
        ];
    }

    private function optionValidationAttributes(): array
    {
        return ['size' => '옵션명', 'size.*' => '옵션명', 'sku' => '옵션코드', 'sku.*' => '옵션코드',
            'price' => '옵션 판매가', 'price.*' => '옵션 판매가', 'stock' => '재고', 'stock.*' => '재고',
            'attributeId' => '옵션 번호', 'attributeId.*' => '옵션 번호'];
    }

    public function deleteAttribute($id)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
        $admin = Auth::guard('admin')->user();
        $attribute = ProductsAttribute::whereKey($id)
            ->when($admin->type === 'vendor', function ($query) use ($admin) {
                $query->whereHas('product', fn ($productQuery) => $productQuery->where('vendor_id', $admin->vendor_id));
            })
            ->lockForUpdate()->firstOrFail();

        $attribute->delete();

        return redirect()->back()->with('success_message', '상품 옵션이 삭제되었습니다.');
        });
    }

    public function addImages(Request $request, $id) // $id는 URL에서 전달된 파라미터(슬러그)입니다.
    {
        Session::put('page', 'products');

        $product = $this->productQuery()
            ->select('id', 'product_name', 'product_code', 'product_color', 'product_price', 'product_image')
            ->with('images')
            ->findOrFail($id); // with('images')는 Product.php 모델의 관계 메소드 이름입니다.

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'images' => ['required', 'array', 'min:1', 'max:10'],
                'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120',
                    'dimensions:max_width=4096,max_height=4096'],
            ], [
                'images.required' => '등록할 이미지를 선택해 주세요.',
                'images.array' => '이미지 파일 선택 형식을 확인해 주세요.',
                'images.min' => '등록할 이미지를 한 개 이상 선택해 주세요.',
                'images.max' => '이미지는 한 번에 최대 10개까지 등록할 수 있습니다.',
                'images.*.required' => '이미지 파일을 확인해 주세요.',
                'images.*.image' => '정상적인 이미지 파일을 선택해 주세요.',
                'images.*.mimes' => 'JPG, PNG, WEBP 이미지 파일만 등록할 수 있습니다.',
                'images.*.max' => '이미지는 파일당 5MB 이하로 선택해 주세요.',
                'images.*.dimensions' => '이미지의 가로·세로는 각각 4,096px 이하여야 합니다.',
                'images.*.uploaded' => '이미지 업로드에 실패했습니다. 파일 용량과 형식을 확인해 주세요.',
            ]);
            app(\App\Services\AdminProductImageUpload::class)->store((int) $id, $data['images']);

            return redirect('admin/add-images/'.$id)->with('success_message', '추가 이미지가 등록되었습니다.');
        }

        return view('admin.images.add_images')->with(compact('product'));
    }

    public function updateImageStatus(Request $request) // add_images.blade.php에서 AJAX를 사용한 이미지 상태 업데이트
    {
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if (in_array($data['status'], ['Active','활성'],true)) { // $data['status']는 $.ajax() 메소드 내 'data' 객체에서 가져옵니다. // 'status'를 (활성/비활성) 0에서 1로, 1에서 0으로 전환
                $status = 0;
            } else {
                $status = 1;
            }

            $image = $this->imageQuery()->findOrFail($data['image_id']);
            $image->status = $status;
            $image->save();

            return response()->json([ // JSON 응답: https://laravel.com/docs/9.x/responses#json-responses
                'status' => $status,
                'image_id' => $data['image_id'],
            ]);
        }
    }

    public function deleteImage($id) // admin/js/custom.js의 AJAX 호출 // 서버와 데이터베이스에서 상품 이미지 삭제 // $id는 라우트 파라미터로 전달됩니다.
    {// 데이터베이스에 저장된 상품 이미지 레코드 가져오기
        $productImage = $this->imageQuery()->select('id', 'image')->findOrFail($id);
        // dd($productImage);

        // 서버 상의 상품 이미지 세 가지 경로 ('small', 'medium', 'large' 폴더) 가져오기
        $small_image_path = public_path('front/images/product_images/small/');
        $medium_image_path = public_path('front/images/product_images/medium/');
        $large_image_path = public_path('front/images/product_images/large/');

        // 서버에서 상품 이미지 삭제 (세 개의 폴더 모두에서)
        // 첫 번째: 'small' 폴더에서 삭제
        if (file_exists($small_image_path.$productImage->image)) {
            unlink($small_image_path.$productImage->image);
        }

        // 두 번째: 'medium' 폴더에서 삭제
        if (file_exists($medium_image_path.$productImage->image)) {
            unlink($medium_image_path.$productImage->image);
        }

        // 세 번째: 'large' 폴더에서 삭제
        if (file_exists($large_image_path.$productImage->image)) {
            unlink($large_image_path.$productImage->image);
        }

        // `products_images` 데이터베이스 테이블에서 상품 이미지 이름(레코드) 삭제
        $productImage->delete();

        $message = '상품 이미지가 성공적으로 삭제되었습니다!';

        return redirect()->back()->with('success_message', $message);
    }

    private function productQuery()
    {
        $admin = Auth::guard('admin')->user();

        return Product::query()->when(
            $admin->type === 'vendor',
            fn ($query) => $query->where('vendor_id', $admin->vendor_id)
        );
    }

    private function attributeQuery()
    {
        $admin = Auth::guard('admin')->user();

        return ProductsAttribute::query()->when(
            $admin->type === 'vendor',
            fn ($query) => $query->whereHas(
                'product',
                fn ($productQuery) => $productQuery->where('vendor_id', $admin->vendor_id)
            )
        );
    }

    private function imageQuery()
    {
        $admin = Auth::guard('admin')->user();

        return ProductsImage::query()->when(
            $admin->type === 'vendor',
            fn ($query) => $query->whereHas(
                'product',
                fn ($productQuery) => $productQuery->where('vendor_id', $admin->vendor_id)
            )
        );
    }
}
