<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;



    // Rating `ratings` 테이블과 User `users` 테이블의 관계 (각 rating은 사용자에 속함)    
    public function user() { // A Rating `ratings` belongs to a User `users`, and the Foreign Key of the Relationship is the `user_id` column in `ratings` table
        return $this->belongsTo('App\Models\User', 'user_id'); // 'user_id' is the Foreign Key of the Relationship
    }

    // Rating `ratings` 테이블과 Product `products` 테이블의 관계 (각 rating은 제품에 속함)    
    public function product() { // A Rating `ratings` belongs to a Product `products`, and the Foreign Key of the Relationship is the `product_id` column in `ratings` table
        return $this->belongsTo('App\Models\Product', 'product_id'); // 'product_id' is the Foreign Key of the Relationship
    }

}