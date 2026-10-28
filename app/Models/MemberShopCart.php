<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MemberShopCart extends Model {
    protected $guarded=[];
    public function shopChannelProduct(){return $this->belongsTo(ShopChannelProduct::class);}
    public function shopChannel(){return $this->belongsTo(ShopChannel::class);}
}
