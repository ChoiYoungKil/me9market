<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Category;

class Product extends Model
{
    use HasFactory;



    // 모든 상품은 하나의 섹션에 속합니다.
    public function section() {
        return $this->belongsTo('App\Models\Section', 'section_id'); 
    }

    // 모든 상품은 하나의 카테고리에 속합니다.
    public function category() {
        return $this->belongsTo('App\Models\Category', 'category_id'); 
    }

    public function parentCategory() {
        return $this->hasOneThrough(
            Category::class,
            Category::class,
            'id', // Foreign key on intermediate: intermediate.id = products.category_id
            'id', // Foreign key on target: target.id = intermediate.parent_id
            'category_id', // Local key on product
            'parent_id' // Local key on intermediate
        )->from('categories as parent_categories');
    }

    public function brand() { // 모든 상품은 특정 브랜드에 속합니다.
        return $this->belongsTo('App\Models\Brand', 'brand_id'); 
    }

    // 모든 상품은 여러 속성을 가집니다.
    public function attributes() {
        return $this->hasMany('App\Models\ProductsAttribute');
    }

    // 모든 상품은 여러 이미지를 가집니다.
    public function images() {
        return $this->hasMany('App\Models\ProductsImage');
    }


    protected $fillable = [
        'section_id', 'category_id', 'brand_id', 'vendor_id', 'admin_id', 'admin_type',
        'product_name', 'product_code', 'product_color', 'product_price', 'product_discount',
        'product_weight', 'product_image', 'product_video', 'group_code', 'description',
        'meta_title', 'meta_keywords', 'meta_description', 'is_featured', 'status',
        'parent_id', 'is_public', 'is_partial', 'partial_approved'
    ];

    // Parent Product Relationship
    public function parentProduct()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    // Children Products Relationship (Partial Products)
    public function partialProducts()
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    // 상품과 입점업체의 관계 (모든 상품은 하나의 입점업체에 속함)
    public function vendor() {    
        return $this->belongsTo('App\Models\Vendor', 'vendor_id')->with('vendorbusinessdetails'); 
    }



    // 상품의 최종 할인 가격을 결정하는 정적 메소드 (카테고리 할인 또는 상품 할인 중 적용)
    public static function getDiscountPrice($product_id) { 
        // 상품 가격, 할인율 및 카테고리 ID 가져오기
        $productDetails = Product::select('product_price', 'product_discount', 'category_id')->where('id', $product_id)->first();
        $productDetails = json_decode(json_encode($productDetails), true); 

        // 카테고리 테이블에서 해당 상품의 카테고리 할인율 가져오기
        $categoryDetails = Category::select('category_discount')->where('id', $productDetails['category_id'])->first();
        $categoryDetails = json_decode(json_encode($categoryDetails), true); 

        
        if ($productDetails['product_discount'] > 0) { // 상품 자체 할인이 있는 경우
            $discounted_price = $productDetails['product_price'] - ($productDetails['product_price'] * $productDetails['product_discount'] / 100);
        } else if ($categoryDetails['category_discount'] > 0) { // 상품 할인은 없지만 카테고리 할인이 있는 경우
            $discounted_price = $productDetails['product_price'] - ($productDetails['product_price'] * $categoryDetails['category_discount'] / 100);
        } else { // 할인이 없는 경우
            $discounted_price = 0;
        }


        return $discounted_price;
    }


    
    public static function getDiscountAttributePrice($product_id, $size) { 
        // 특정 상품 ID와 사이즈에 해당하는 속성 정보 가져오기
        $proAttrPrice = \App\Models\ProductsAttribute::where([ 
            'product_id' => $product_id,
            'size'       => $size
        ])->first()->toArray();

        // 상품의 할인율 및 카테고리 ID 가져오기
        $proDetails = Product::select('product_discount', 'category_id')->where('id', $product_id)->first();
        $proDetails = json_decode(json_encode($proDetails), true); 

        // 카테고리 할인율 가져오기
        $catDetails = Category::select('category_discount')->where('id', $proDetails['category_id'])->first();
        $catDetails = json_decode(json_encode($catDetails), true); 

        if ($proDetails['product_discount'] > 0) { // 상품 할인이 있는 경우
            // 상품 자체에 할인이 있는 경우
            $final_price = $proAttrPrice['price'] - ($proAttrPrice['price'] * $proDetails['product_discount'] / 100);
            $discount = $proAttrPrice['price'] - $final_price; // 할인 금액 = 원래 가격 - 할인 후 가격

        } else if ($catDetails['category_discount'] > 0) { // 상품 할인은 없지만 카테고리 할인이 있는 경우
            // 상품 할인은 없지만 해당 상품 카테고리 전체에 할인이 있는 경우
            $final_price = $proAttrPrice['price'] - ($proAttrPrice['price'] * $catDetails['category_discount'] / 100);
            $discount = $proAttrPrice['price'] - $final_price; // 할인 금액 = 원래 가격 - 할인 후 가격

        // 참고: 'product_discount' (products 테이블)와 'category_discount' (categories 테이블) 할인이 동시에 적용되는 경우는 고려되지 않았습니다.
        } else { // product_discount (products 테이블) 또는 category_discount (categories 테이블) 모두 할인이 없는 경우
            $final_price = $proAttrPrice['price'];
            $discount = 0;
        }


        return array(
            'product_price' => $proAttrPrice['price'], // products_attributes 테이블에서 해당 product_id와 size의 원래 가격
            'final_price'   => $final_price,           // product_id와 size에 해당하는 products_attributes 테이블의 가격에서 할인(product_discount 또는 category_discount)이 적용된 최종 가격
            'discount'      => $discount               // 할인 금액 (있는 경우)
        );
    }



    public static function isProductNew($product_id) { 
        // 최근에 추가된 상품 3개의 ID 가져오기
        $productIds = Product::select('id')->where('status', 1)->orderBy('id', 'Desc')->limit(3)->pluck('id');
        $productIds = json_decode(json_encode($productIds, true));

        if (in_array($product_id, $productIds)) { // 전달된 상품 ID가 최근 추가된 3개 상품 중 하나인 경우
            $isProductNew = 'Yes';
        } else {
            $isProductNew = 'No';
        }


        return $isProductNew;
    }



    
    public static function getProductImage($product_id) { // 이 메소드는 front/orders/order_details.blade.php에서 사용됩니다.
        $getProductImage = Product::select('product_image')->where('id', $product_id)->first()->toArray();


        return $getProductImage['product_image'];
    }

    
    // 참고: '비활성화'된 상품(status = 0)의 주문(결제 시)을 방지해야 합니다. 상품 자체는 admin/products/products.blade.php에서 비활성화될 수 있으며(products 데이터베이스 테이블 확인), 상품의 속성(재고)은 'admin/attributes/add_edit_attributes.blade.php'에서 비활성화될 수 있습니다(products_attributes 데이터베이스 테이블 확인). 또한 품절된 상품의 주문도 방지합니다(products_attributes 데이터베이스 테이블 확인).
    public static function getProductStatus($product_id) {
        $getProductStatus = Product::select('status')->where('id', $product_id)->first();


        return $getProductStatus->status;
    }

    // 상품이 '비활성화'되었거나 품절된 경우 장바구니에서 삭제
    public static function deleteCartProduct($product_id) {
        Cart::where('product_id', $product_id)->delete();
    }

}