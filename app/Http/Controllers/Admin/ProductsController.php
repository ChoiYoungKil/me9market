<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

use App\Models\Product;
use App\Models\ProductsImage;
use App\Models\ProductsFilter;
use App\Models\ProductsAttribute;


class ProductsController extends Controller
{
    public function products() { // 관리자 패널의 상품 목록 페이지 렌더링
        Session::put('page', 'products');


        // 입점업체인 경우 본인의 상품만 표시하며, 계정이 활성화된 상태인지 확인합니다.
        $adminType = Auth::guard('admin')->user()->type; 
        $vendor_id = Auth::guard('admin')->user()->vendor_id; 

        if ($adminType == 'vendor') { // 로그인한 사용자가 'vendor'인 경우 상태 확인
            $vendorStatus = Auth::guard('admin')->user()->status; 
            if ($vendorStatus == 0) { // 입점업체 계정이 비활성화된 경우
                return redirect('admin/update-vendor-details/personal')->with('error_message', '입점업체 계정이 아직 승인되지 않았습니다. 개인, 사업자 및 은행 정보를 정확히 입력해 주세요.'); 
            }
        }

        // 모든 상품 가져오기 ($products)
        $products = Product::with([ 
            'section' => function($query) { 
                $query->select('id', 'name'); 
            },
            'category' => function($query) { 
                $query->select('id', 'category_name'); 
            }
        ]);

        // 입점업체인 경우 본인의 상품만 필터링
        if ($adminType == 'vendor') {
            $produtcs = $products->where('vendor_id', $vendor_id);
        }

        $products = $products->get()->toArray();
        // dd($products);


        return view('admin.products.products')->with(compact('products')); 
    }

    public function updateProductStatus(Request $request) { // AJAX를 사용하여 상품 상태 업데이트
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if ($data['status'] == 'Active') { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }


            Product::where('id', $data['product_id'])->update(['status' => $status]); 
            // echo '<pre>', var_dump($data), '</pre>';

            return response()->json([ 
                'status'     => $status,
                'product_id' => $data['product_id']
            ]);
        }
    }

    public function deleteProduct($id) {
        Product::where('id', $id)->delete();

        $message = '상품이 성공적으로 삭제되었습니다!';

        return redirect()->back()->with('success_message', $message);
    }

    public function addEditProduct(Request $request, $id = null) { // 상품 추가 또는 수정
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'products');


        if ($id == '') { // $id가 없으면 상품 추가
            $title = '상품 추가';
            $product = new \App\Models\Product();
            // dd($product);
            $message = '상품이 성공적으로 추가되었습니다!';
        } else { // $id가 있으면 상품 수정
            $title = '상품 수정';
            $product = Product::find($id);
            // dd($product);
            $message = '상품이 성공적으로 업데이트되었습니다!';
        }

        if ($request->isMethod('post')) { // 폼 제출인 경우 (추가 또는 수정 공용)
            $data = $request->all();
            // dd($data);


            // 라라벨 유효성 검사
            $rules = [
                'category_id'   => 'required',
                'product_name'  => 'required', 
                'product_code'  => 'required|regex:/^\w+$/', 
                'product_price' => 'required|numeric',
                'product_color' => 'required|regex:/^[\pL\s\-]+$/u', 
            ];

            $customMessages = [ 
                'category_id.required'   => '카테고리를 선택해 주세요',
                'product_name.required'  => '상품명을 입력해 주세요',
                'product_name.regex'     => '유효한 상품명을 입력해 주세요',
                'product_code.required'  => '상품 코드를 입력해 주세요',
                'product_code.regex'     => '유효한 상품 코드를 입력해 주세요',
                'product_price.required' => '상품 가격을 입력해 주세요',
                'product_price.numeric'  => '유효한 상품 가격을 입력해 주세요',
                'product_color.required' => '상품 색상을 입력해 주세요',
                'product_color.regex'    => '유효한 상품 색상을 입력해 주세요',

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
                    $imageName = rand(111, 99999) . '.' . $extension; 

                    // 'public' 폴더 내 업로드된 이미지 경로 할당
                    // 이미지 크기에 따라 small, medium, large 세 개의 폴더를 사용합니다.
                    $largeImagePath  = 'front/images/product_images/large/'  . $imageName; // 'large'  이미지 폴더
                    $mediumImagePath = 'front/images/product_images/medium/' . $imageName; // 'medium' 이미지 폴더
                    $smallImagePath  = 'front/images/product_images/small/'  . $imageName; // 'small'  이미지 폴더

                    // 'Intervention' 패키지를 사용하여 이미지 업로드 및 'public' 폴더 내 세 개의 경로(폴더)에 저장
                    Image::make($image_tmp)->resize(1000, 1000)->save($largeImagePath);  // 'large'  이미지 크기로 리사이즈 후 'large'  폴더에 저장
                    Image::make($image_tmp)->resize(500,   500)->save($mediumImagePath); // 'medium' 이미지 크기로 리사이즈 후 'medium' 폴더에 저장
                    Image::make($image_tmp)->resize(250,   250)->save($smallImagePath);  // 'small'  이미지 크기로 리사이즈 후 'small'  폴더에 저장

                    // 데이터베이스에 이미지 이름 저장
                    $product->product_image = $imageName;
                }
            }


            // 상품 동영상 업로드
            if ($request->hasFile('product_video')) {
                $video_tmp = $request->file('product_video');

                if ($video_tmp->isValid()) { // 성공적인 업로드 검증: https://laravel.com/docs/9.x/requests#validating-successful-uploads
                    // 동영상 업로드
                    $extension  = $video_tmp->getClientOriginalExtension();

                    // 업로드된 동영상의 새로운 랜덤 이름 생성 (파일명 중복으로 인한 덮어쓰기 방지)
                    $videoName = rand() . '.' . $extension; // e.g.    75935.mp4

                    // 'public' 폴더 내 업로드된 동영상 경로 할당
                    $videoPath = 'front/videos/product_videos/';

                    // 임시 경로(웹 서버 할당)에서 'public' 폴더 내 지정된 경로로 동영상 이동    // 파일 복사 및 이동: https://laravel.com/docs/9.x/filesystem#copying-moving-files
                    $video_tmp->move($videoPath, $videoName);

                    // 데이터베이스 테이블에 동영상 이름 저장
                    $product->product_video = $videoName;
                }
            }


            // 새 상품 추가 및 기존 상품 업데이트 정보를 `products` 테이블에 저장
            $categoryDetails = \App\Models\Category::find($data['category_id']); 
            // dd($categoryDetails);

            $product->section_id  = $categoryDetails['section_id'];
            $product->category_id = $data['category_id'];
            $product->brand_id    = $data['brand_id'];
            $product->group_code  = $data['group_code']; // 상품 색상 관리를 위한 그룹 코드


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
                $admin_id  = Auth::guard('admin')->user()->id; 
 
                $product->admin_type = $adminType;
                $product->admin_id   = $admin_id;
 
                if ($adminType == 'vendor') {
                    $product->vendor_id  = $vendor_id;
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


            $product->product_name     = $data['product_name'];
            $product->product_code     = $data['product_code'];
            $product->product_color    = $data['product_color'];
            $product->product_price    = $data['product_price'];
            $product->product_discount = $data['product_discount'];
            $product->product_weight   = $data['product_weight'];
            $product->description      = $data['description'];
            $product->meta_title       = $data['meta_title'];
            $product->meta_description = $data['meta_description'];
            $product->meta_keywords    = $data['meta_keywords'];



            if (!empty($data['is_featured'])) {
                // dd($data);
                $product->is_featured = $data['is_featured'];
            } else {
                // dd($data);
                $product->is_featured = 'No';
            }


            if (!empty($data['is_bestseller'])) {
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
        $categories = \App\Models\Section::with('categories')->get()->toArray(); // with('categories')는 Section.php 모델의 관계 메소드 이름입니다.
        // dd($categories);

        // 모든 브랜드 가져오기
        $brands = \App\Models\Brand::where('status', 1)->get()->toArray();
        // dd($brands);


        // return view('admin.products.add_edit_product')->with(compact('title', 'product'));
        return view('admin.products.add_edit_product')->with(compact('title', 'product', 'categories', 'brands'));
    }

    public function deleteProductImage($id) { // admin/js/custom.js의 AJAX 호출 - 서버와 DB에서 상품 이미지 삭제
        // 데이터베이스에 저장된 상품 이미지 레코드 가져오기
        $productImage = Product::select('product_image')->where('id', $id)->first();
        // dd($productImage);

        // 서버 상의 상품 이미지 3단계 경로 (small, medium, large 폴더)
        $small_image_path  = 'front/images/product_images/small/';
        $medium_image_path = 'front/images/product_images/medium/';
        $large_image_path  = 'front/images/product_images/large/';

        // 서버에서 실제 상품 이미지 삭제 (3개 폴더 모두)
        // 첫 번째: 'small' 폴더에서 삭제
        if (file_exists($small_image_path . $productImage->product_image)) {
            unlink($small_image_path . $productImage->product_image);
        }

        // 두 번째: 'medium' 폴더에서 삭제
        if (file_exists($medium_image_path . $productImage->product_image)) {
            unlink($medium_image_path . $productImage->product_image);
        }

        // 세 번째: 'large' 폴더에서 삭제
        if (file_exists($large_image_path . $productImage->product_image)) {
            unlink($large_image_path . $productImage->product_image);
        }


        // products 테이블에서 상품 이미지 레코드 삭제 (컬럼 값을 빈 문자열로 업데이트)
        Product::where('id', $id)->update(['product_image' => '']);

        $message = '상품 이미지가 성공적으로 삭제되었습니다!';


        return redirect()->back()->with('success_message', $message);
    }

    public function deleteProductVideo($id) { // admin/js/custom.js의 AJAX 호출 - 서버와 DB에서 상품 동영상 삭제
        // 데이터베이스에 저장된 상품 동영상 레코드 가져오기
        $productVideo = Product::select('product_video')->where('id', $id)->first();
        // dd($productVideo);

        // 서버 상의 상품 동영상 경로
        $product_video_path = 'front/videos/product_videos/';

        // 서버에서 상품 동영상 삭제
        if (file_exists($product_video_path . $productVideo->product_video)) {
            unlink($product_video_path . $productVideo->product_video);
        }

        // products 테이블에서 상품 동영상 레코드 삭제 (컬럼 값을 빈 문자열로 업데이트)
        Product::where('id', $id)->update(['product_video' => '']);

        $message = '상품 동영상이 성공적으로 삭제되었습니다!';

        return redirect()->back()->with('success_message', $message);
    }

    public function addAttributes(Request $request, $id) { // 속성 추가/수정 함수
        Session::put('page', 'products');

        $product = Product::select('id', 'product_name', 'product_code', 'product_color', 'product_price', 'product_image')->with('attributes')->find($id); // with('attributes')는 Product.php 모델의 관계 메소드 이름입니다.

        if ($request->isMethod('post')) { // 폼이 제출되었을 때
            $data = $request->all();
            // dd($data);

            foreach ($data['sku'] as $key => $value) { // SKU, 사이즈, 가격 또는 재고 데이터 처리
                // echo '<pre>', var_dump($key), '</pre>';
                // echo '<pre>', var_dump($value), '</pre>';
                
                if (!empty($value)) {
                    // 유효성 검사:
                    // SKU 중복 체크 (SKU는 각 상품별로 고유해야 하므로 중복 방지)
                    $skuCount = ProductsAttribute::where('sku', $value)->count();
                    if ($skuCount > 0) { // 상품의 SKU가 이미 존재하는 경우
                        return redirect()->back()->with('error_message', 'SKU가 이미 존재합니다! 다른 SKU를 추가해 주세요!');
                    }

                    // 사이즈 중복 체크 (사이즈는 각 상품별로 고유해야 하므로 중복 방지)
                    $sizeCount = ProductsAttribute::where(['product_id' => $id, 'size' => $data['size'][$key]])->count();
                    if ($sizeCount > 0) { // 이미 존재하는 경우
                        return redirect()->back()->with('error_message', '사이즈가 이미 존재합니다! 다른 사이즈를 추가해 주세요!');
                    }


                    $attribute = new ProductsAttribute;

                    $attribute->product_id = $id; // $id는 addAttributes() 메소드로 전달된 값입니다.
                    $attribute->sku        = $value;
                    $attribute->size       = $data['size'][$key];  // $key는 반복/루프 횟수를 나타냅니다 (0, 1, 2, ...)
                    $attribute->price      = $data['price'][$key];
                    $attribute->stock      = $data['stock'][$key];
                    $attribute->status     = 1;
                    
                    $attribute->save();
                }
            }
            return redirect()->back()->with('success_message', '상품 속성이 성공적으로 추가되었습니다!');
        }


        return view('admin.attributes.add_edit_attributes')->with(compact('product'));
    }

    public function updateAttributeStatus(Request $request) { // add_edit_attributes.blade.php에서 AJAX를 사용한 속성 상태 업데이트
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if ($data['status'] == 'Active') { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }


            ProductsAttribute::where('id', $data['attribute_id'])->update(['status' => $status]); // $data['attribute_id']는 $.ajax() 메소드 내 'data' 객체에서 가져옵니다.

            return response()->json([ 
                'status'       => $status,
                'attribute_id' => $data['attribute_id']
            ]);
        }
    }

    public function editAttributes(Request $request) {
        Session::put('page', 'products');

        if ($request->isMethod('post')) { // 폼이 제출되었을 때
            $data = $request->all();
            // dd($data);

            foreach ($data['attributeId'] as $key => $attribute) {
                if (!empty($attribute)) {
                    ProductsAttribute::where([
                        'id' => $data['attributeId'][$key]
                    ])->update([
                        'price' => $data['price'][$key],
                        'stock' => $data['stock'][$key]
                    ]);
                }
            }

            return redirect()->back()->with('success_message', '상품 속성이 성공적으로 업데이트되었습니다!');
        }
    }

    public function addImages(Request $request, $id) { // $id는 URL에서 전달된 파라미터(슬러그)입니다.
        Session::put('page', 'products');

        $product = Product::select('id', 'product_name', 'product_code', 'product_color', 'product_price', 'product_image')->with('images')->find($id); // with('images')는 Product.php 모델의 관계 메소드 이름입니다.


        if ($request->isMethod('post')) { // 폼이 제출되었을 때
            $data = $request->all();
            // dd($data);

            if ($request->hasFile('images')) {
                $images = $request->file('images');
                // dd($images);

                foreach ($images as $key => $image) {
                    // 이미지 업로딩:
                    // 임시 이미지 생성
                    $image_tmp = Image::make($image);

                    // 이미지 이름 가져오기
                    $image_name = $image->getClientOriginalName();
                    // dd($image_tmp);

                    // 이미지 확장자 가져오기
                    $extension = $image->getClientOriginalExtension();

                    // 이미지 중복 방지를 위한 랜덤 이름 생성
                    $imageName = $image_name . rand(111, 99999) . '.' . $extension; // 예: 5954.png

                    // 'public' 폴더 내 업로드된 이미지 경로 설정
                    // 이미지 크기에 따라 small, medium, large 세 개의 폴더를 가집니다.
                    $largeImagePath  = 'front/images/product_images/large/'  . $imageName; // 'large' 이미지 폴더
                    $mediumImagePath = 'front/images/product_images/medium/' . $imageName; // 'medium' 이미지 폴더
                    $smallImagePath  = 'front/images/product_images/small/'  . $imageName; // 'small' 이미지 폴더

                    // 'Intervention' 패키지를 사용하여 이미지 업로드 및 세 개의 경로(폴더)에 저장
                    Image::make($image_tmp)->resize(1000, 1000)->save($largeImagePath);  // 'large' 이미지 크기 조정 후 'large' 폴더에 저장
                    Image::make($image_tmp)->resize(500,   500)->save($mediumImagePath); // 'medium' 이미지 크기 조정 후 'medium' 폴더에 저장
                    Image::make($image_tmp)->resize(250,   250)->save($smallImagePath);  // 'small' 이미지 크기 조정 후 'small' 폴더에 저장
                
                    // `products_images` 데이터베이스 테이블에 이미지 이름 삽입
                    $image = new ProductsImage;

                    $image->image      = $imageName;
                    $image->product_id = $id;
                    $image->status     = 1;

                    $image->save();
                }
            }

            return redirect()->back()->with('success_message', '상품 이미지가 성공적으로 추가되었습니다!');
        }


        return view('admin.images.add_images')->with(compact('product'));
    }

    public function updateImageStatus(Request $request) { // add_images.blade.php에서 AJAX를 사용한 이미지 상태 업데이트
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if ($data['status'] == 'Active') { // $data['status']는 $.ajax() 메소드 내 'data' 객체에서 가져옵니다. // 'status'를 (활성/비활성) 0에서 1로, 1에서 0으로 전환
                $status = 0;
            } else {
                $status = 1;
            }


            ProductsImage::where('id', $data['image_id'])->update(['status' => $status]); // $data['image_id']는 $.ajax() 메소드 내 'data' 객체에서 가져옵니다.

            return response()->json([ // JSON 응답: https://laravel.com/docs/9.x/responses#json-responses
                'status'   => $status,
                'image_id' => $data['image_id']
            ]);
        }
    }

    public function deleteImage($id) { // admin/js/custom.js의 AJAX 호출 // 서버와 데이터베이스에서 상품 이미지 삭제 // $id는 라우트 파라미터로 전달됩니다.
        // 데이터베이스에 저장된 상품 이미지 레코드 가져오기
        $productImage = ProductsImage::select('image')->where('id', $id)->first();
        // dd($productImage);

        // 서버 상의 상품 이미지 세 가지 경로 ('small', 'medium', 'large' 폴더) 가져오기
        $small_image_path  = 'front/images/product_images/small/';
        $medium_image_path = 'front/images/product_images/medium/';
        $large_image_path  = 'front/images/product_images/large/';

        // 서버에서 상품 이미지 삭제 (세 개의 폴더 모두에서)
        // 첫 번째: 'small' 폴더에서 삭제
        if (file_exists($small_image_path . $productImage->image)) {
            unlink($small_image_path . $productImage->image);
        }

        // 두 번째: 'medium' 폴더에서 삭제
        if (file_exists($medium_image_path . $productImage->image)) {
            unlink($medium_image_path . $productImage->image);
        }

        // 세 번째: 'large' 폴더에서 삭제
        if (file_exists($large_image_path . $productImage->image)) {
            unlink($large_image_path . $productImage->image);
        }


        // `products_images` 데이터베이스 테이블에서 상품 이미지 이름(레코드) 삭제
        ProductsImage::where('id', $id)->delete();

        $message = '상품 이미지가 성공적으로 삭제되었습니다!';

        return redirect()->back()->with('success_message', $message);
    }

}