<?php
namespace App\Services;

use App\Models\{MemberShopCart,ShopChannel,ShopChannelProduct,User};
use Illuminate\Support\Facades\{Auth,DB,Session};

class MemberCart
{
    private const SESSION_KEY='shop_channel_cart';
    private const OWNER_KEY='shop_cart_member_id';

    public function mutate(callable $callback): mixed
    {
        if (!Auth::check()) return $callback();
        return DB::transaction(function () use ($callback) {
            User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();
            return $callback();
        });
    }

    public function read(ShopChannel $shop): array
    {
        if (!Auth::check()) return Session::get(self::SESSION_KEY,[]);
        if ((int)Session::get(self::OWNER_KEY,0)!==Auth::id()) {
            $this->mutate(function () {
                // Adopt a guest cart once. Never import a different member's snapshot.
                if (!Session::has(self::OWNER_KEY)) {
                    foreach(Session::get(self::SESSION_KEY,[]) as $key=>$row) {
                        $product=ShopChannelProduct::find((int)$key);
                        if (!$product || !preg_match('/^[0-9]+(?::[a-f0-9]{16})?$/',(string)$key)) continue;
                        $option=mb_substr((string)($row['option']??'기본옵션'),0,100);
                        $entry=MemberShopCart::firstOrNew(['user_id'=>Auth::id(),'cart_key'=>(string)$key]);
                        $entry->fill(['shop_channel_id'=>$product->shop_channel_id,'shop_channel_product_id'=>$product->id,'option_name'=>$option,'quantity'=>min(999,max(1,(int)($row['qty']??1))+(int)($entry->quantity??0))])->save();
                    }
                }
                Session::put(self::OWNER_KEY,Auth::id());
            });
        }
        return MemberShopCart::where('user_id',Auth::id())->where('shop_channel_id',$shop->id)->get()
            ->mapWithKeys(fn($entry)=>[$entry->cart_key=>['qty'=>(int)$entry->quantity,'option'=>$entry->option_name]])->all();
    }

    public function save(ShopChannel $shop,array $cart): void
    {
        if (Auth::check()) {
            $this->mutate(function () use ($shop,$cart) {
                $query=MemberShopCart::where('user_id',Auth::id())->where('shop_channel_id',$shop->id);
                if ($cart) $query->whereNotIn('cart_key',array_keys($cart));
                $query->delete();
                foreach($cart as $key=>$row) {
                    $product=ShopChannelProduct::where('shop_channel_id',$shop->id)->find((int)$key);
                    if (!$product) continue;
                    MemberShopCart::updateOrCreate(['user_id'=>Auth::id(),'cart_key'=>(string)$key],[
                        'shop_channel_id'=>$shop->id,'shop_channel_product_id'=>$product->id,'option_name'=>$row['option'],'quantity'=>$row['qty'],
                    ]);
                }
                Session::put(self::OWNER_KEY,Auth::id());
            });
        }
        Session::put(self::SESSION_KEY,$cart);
    }

    public function clear(ShopChannel $shop): void
    {
        if (Auth::check()) MemberShopCart::where('user_id',Auth::id())->where('shop_channel_id',$shop->id)->delete();
        Session::forget(self::SESSION_KEY);
    }
}
