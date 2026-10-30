<?php
namespace App\Services;
use App\Models\{Product,ShopChannel,ShopChannelProduct};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ShopCatalog
{
    public function data(Request $request,ShopChannel $shop): array
    {
        $f=$request->validate(['catalog_type'=>'nullable|in:own,public,partial','catalog_q'=>'nullable|string|max:150',
            'catalog_category'=>'nullable|integer','catalog_seller'=>'nullable|string|max:150','catalog_min'=>'nullable|numeric|min:0',
            'catalog_max'=>'nullable|numeric|min:0','catalog_states'=>'nullable|array','catalog_states.*'=>'in:new,pending,approved,rejected']);
        if(isset($f['catalog_min'],$f['catalog_max'])&&$f['catalog_max']<$f['catalog_min'])throw \Illuminate\Validation\ValidationException::withMessages(['catalog_max'=>'최고가격은 최저가격 이상이어야 합니다.']);
        $type=$f['catalog_type']??'own';
        if($shop->use_own_pg)$type='own';
        $listings=ShopChannelProduct::where('shop_channel_id',$shop->id)->get()->keyBy('product_id');
        $favorites=DB::table('shop_product_favorites')->where('vendor_id',$shop->vendor_id)->pluck('product_id')->all();
        $q=Product::with(['category','images','vendor.vendorbusinessdetails'])->where('status',1);
        if($type==='own')$q->where('vendor_id',$shop->vendor_id);
        else $q->where('vendor_id','!=',$shop->vendor_id)->where($type==='public'?'is_public':'is_partial','Yes');
        $excluded=$listings->filter(fn($r)=>$r->product_type!=='partial'||($r->approval_status==='approved'&&$r->listed_at))->keys();
        $q->whereNotIn('id',$excluded);
        if(filled($f['catalog_q']??null))$q->where(fn($b)=>$b->where('product_name','like','%'.$f['catalog_q'].'%')->orWhere('product_code','like','%'.$f['catalog_q'].'%'));
        if(filled($f['catalog_seller']??null))$q->whereHas('vendor',fn($b)=>$b->where('name','like','%'.$f['catalog_seller'].'%')->orWhere('email','like','%'.$f['catalog_seller'].'%'));
        if(!empty($f['catalog_category'])){
            $ids=[(int)$f['catalog_category']];$cursor=$ids;
            while($cursor){$cursor=\App\Models\Category::whereIn('parent_id',$cursor)->whereNotIn('id',$ids)->pluck('id')->all();$ids=array_merge($ids,$cursor);}
            $q->whereIn('category_id',$ids);
        }
        $lower="CASE WHEN price_constraint_enabled=1 AND price_constraint_type='fixed' THEN COALESCE(price_fixed,product_price) WHEN price_constraint_enabled=1 AND price_constraint_type='range' THEN COALESCE(price_min,product_price) ELSE product_price END";
        $upper="CASE WHEN price_constraint_enabled=1 AND price_constraint_type='fixed' THEN COALESCE(price_fixed,product_price) WHEN price_constraint_enabled=1 AND price_constraint_type='range' THEN COALESCE(price_max,product_price) ELSE product_price END";
        if(isset($f['catalog_min']))$q->whereRaw($upper.' >= ?',[$f['catalog_min']]);
        if(isset($f['catalog_max']))$q->whereRaw($lower.' <= ?',[$f['catalog_max']]);
        if($type==='partial'&&!empty($f['catalog_states'])){
            $q->where(function($b)use($f,$listings){
                $ids=$listings->filter(fn($r)=>in_array($r->approval_status,$f['catalog_states']))->keys();
                $b->whereIn('id',$ids);
                if(in_array('new',$f['catalog_states']))$b->orWhereNotIn('id',$listings->keys());
            });
        }
        if($favorites)$q->orderByRaw('CASE WHEN id IN ('.implode(',',array_map('intval',$favorites)).') THEN 0 ELSE 1 END');
        return ['catalogType'=>$type,'catalogFilters'=>$f,'catalogProducts'=>$q->orderByDesc('id')->paginate(10,['*'],'catalog_page')->withQueryString(),
            'catalogListings'=>$listings,'catalogFavorites'=>$favorites,'catalogCategories'=>\App\Models\Category::where('status',1)->orderBy('category_name')->get()];
    }
}
