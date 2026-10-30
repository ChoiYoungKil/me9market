<?php

namespace App\Services;

use App\Models\{Admin, ShopChannel, ShopChannelPrivateAccess, Vendor};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, File};
use Illuminate\Support\Str;
use Illuminate\Validation\{Rule, ValidationException};

class ShopChannelConfiguration
{
    public function pgReady(?Vendor $vendor): bool
    {
        return $vendor && (int)$vendor->status===1 && $vendor->use_own_pg && collect(['pg_provider','pg_merchant_id','pg_site_code','pg_client_key','pg_secret_key'])->every(fn($field)=>filled($vendor->$field));
    }

    public function keywords($value): array
    {
        for($i=0;$i<2 && is_string($value);$i++)$value=json_decode($value,true)??[];
        return is_array($value)?array_values(array_filter($value,'is_string')):[];
    }

    public function form(Admin $admin, ?ShopChannel $shop = null): array
    {
        $vendor=Vendor::find($admin->vendor_id);
        $privateRows=$shop?->privateAccesses()->orderBy('id')->get()->map(fn($r)=>$r->only(['phone','entry_code','user_id','created_at']))->all()??[];
        return compact('shop','vendor','privateRows')+['pgReady'=>$this->pgReady($vendor), 'channelCode'=>$shop?->channel_code ?? old('channel_code','Me9-'.now()->format('Ymd').'-'.strtoupper(Str::random(8))),
            'shopKeywords'=>$this->keywords($shop?->keywords), 'dep1_id'=>'01'];
    }

    public function save(Request $request, Admin $admin, ?ShopChannel $shop = null): ShopChannel
    {
        $existing=$shop?->banner_images;
        if(is_string($existing))$existing=json_decode($existing,true);
        $existing=is_array($existing)?$existing:[];
        $rules=[
            'channel_code'=>[$shop?'sometimes':'required','string','max:80','alpha_dash',Rule::unique('shop_channels')->ignore($shop?->id)],
            'channel_name'=>'required|string|max:255','copyright'=>'nullable|string|max:255',
            'keywords'=>'required|array|min:1|max:20','keywords.*'=>'required|string|max:50',
            'status'=>'required|boolean','is_public'=>'required|boolean','is_member_only'=>'nullable|boolean',
            'use_period_type'=>'required|boolean','start_date'=>'nullable|required_if:use_period_type,1|date_format:Y-m-d',
            'end_date'=>'nullable|required_if:use_period_type,1|date_format:Y-m-d','start_hour'=>'nullable|required_if:use_period_type,1|integer|between:0,23','end_hour'=>'nullable|required_if:use_period_type,1|integer|between:0,23',
            'use_logo'=>'required|boolean','logo_image'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'use_banner'=>'required|boolean','banner_files'=>'nullable|array|max:5','banner_files.*'=>'image|mimes:jpg,jpeg,png,webp|max:5120',
            'existing_banners'=>'nullable|array|max:5','existing_banners.*'=>['string',Rule::in($existing)],
            'use_og'=>'required|boolean','og_title'=>'nullable|required_if:use_og,1|string|max:255','og_description'=>'nullable|required_if:use_og,1|string|max:500',
            'og_image'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:5120','use_own_pg'=>'nullable|boolean',
            'use_purchase_sms'=>'nullable|boolean','purchase_sms_templates'=>'nullable|array',
            'use_admin'=>'required|boolean','admin_name'=>'nullable|required_if:use_admin,1|string|max:50',
            'admin_login_id'=>['nullable','required_if:use_admin,1','string','max:50','regex:/^[A-Za-z0-9_.@-]+$/',Rule::unique('shop_channels')->ignore($shop?->id)],
            'admin_password'=>['nullable',\Illuminate\Validation\Rules\Password::min(12)->mixedCase()->letters()->numbers()],
            'settlement_type'=>'nullable|in:1,2','settlement_rate_percent'=>'nullable|numeric|between:0,100','settlement_rate_amount'=>'nullable|integer|min:0',
            'password'=>'nullable|string|max:128','private_access_rows'=>'nullable|string|max:300000',
            'private_access_file'=>'nullable|file|max:5120|mimes:xlsx,xls,csv,txt',
        ];
        foreach(['purchase','purchase_confirmed','cancel','return'] as $type)$rules['purchase_sms_templates.'.$type]='nullable|string|max:300';
        $data=$request->validate($rules);
        if($shop?->closure_status==='approved' && $request->boolean('status'))throw ValidationException::withMessages(['status'=>'운영중지 승인된 채널은 다시 운영 상태로 변경할 수 없습니다.']);
        if($shop && $request->filled('channel_code') && $data['channel_code']!==$shop->channel_code)throw ValidationException::withMessages(['channel_code'=>'등록된 채널코드는 변경할 수 없습니다.']);
        $keywords=array_values(array_unique(array_map('trim',$data['keywords'])));
        if(in_array('',$keywords,true))throw ValidationException::withMessages(['keywords'=>'키워드를 한 개 이상 입력해 주세요.']);
        $start=$end=null;
        if($request->boolean('use_period_type')){
            $start=\Carbon\Carbon::parse($data['start_date'])->startOfDay()->setHour((int)$data['start_hour']);
            $end=\Carbon\Carbon::parse($data['end_date'])->startOfDay()->setHour((int)$data['end_hour']);
            if($end->lt($start))throw ValidationException::withMessages(['end_date'=>'종료 날짜와 시간은 시작 이후여야 합니다.']);
        }
        if($request->boolean('use_logo') && !$request->hasFile('logo_image') && !$shop?->logo_image)throw ValidationException::withMessages(['logo_image'=>'로고 이미지를 첨부해 주세요.']);
        if($request->boolean('use_og') && !$request->hasFile('og_image') && !$shop?->og_image)throw ValidationException::withMessages(['og_image'=>'OG 이미지를 첨부해 주세요.']);
        $kept=$data['existing_banners']??[];
        $uploads=$request->file('banner_files',[]);
        if(count($kept)+count($uploads)>5 || ($request->boolean('use_banner') && count($kept)+count($uploads)===0))throw ValidationException::withMessages(['banner_files'=>'배너 이미지는 1개 이상, 최대 5개까지 등록할 수 있습니다.']);
        $vendor=Vendor::find($admin->vendor_id);$ownPg=$request->boolean('use_own_pg');
        if($ownPg && !$this->pgReady($vendor))throw ValidationException::withMessages(['use_own_pg'=>'정보관리에서 자사 PG 등록을 완료한 판매인증 회원사만 사용할 수 있습니다.']);
        if($ownPg && $shop?->shopChannelProducts()->where(fn($q)=>$q->whereIn('product_type',['public','partial'])->orWhereHas('product',fn($q)=>$q->where('reward_points','>',0)))->exists())throw ValidationException::withMessages(['use_own_pg'=>'공유상품 또는 적립포인트 상품이 등록된 채널에는 자사 PG를 적용할 수 없습니다.']);
        if($request->boolean('use_admin')){
            if(!$request->filled('admin_password') && !$shop?->admin_password)throw ValidationException::withMessages(['admin_password'=>'모니터링 관리자 비밀번호를 입력해 주세요.']);
            $rateField=($data['settlement_type']??'1')=='1'?'settlement_rate_percent':'settlement_rate_amount';
            if(!isset($data[$rateField]))throw ValidationException::withMessages([$rateField=>'정산요율을 입력해 주세요.']);
        }
        $import=app(PrivateAccessImport::class);
        $privateRows=$request->boolean('is_public')?[]:$import->normalize(array_merge($import->parse($data['private_access_rows']??'',$data['password']??''),$import->file($request->file('private_access_file'),$data['password']??'')));
        $newFiles=[];
        try{return DB::transaction(function()use($request,$admin,$shop,$data,$keywords,$start,$end,$kept,$uploads,$vendor,$ownPg,$privateRows,&$newFiles){
            $shop=$shop?->newQuery()->whereKey($shop->id)->lockForUpdate()->firstOrFail()??new ShopChannel;
            $shop->vendor_id=$admin->vendor_id;if(!$shop->exists)$shop->channel_code=$data['channel_code'];
            foreach(['channel_name','copyright'] as $field)$shop->$field=$data[$field]??'';
            foreach(['status','is_public','use_period_type','use_logo','use_banner','use_og','use_admin','use_purchase_sms'] as $field)$shop->$field=$request->boolean($field);
            $shop->is_member_only=$shop->is_public?$request->boolean('is_member_only'):false;
            $shop->keywords=$keywords;$shop->start_at=$start;$shop->end_at=$end;
            if($request->filled('password'))$shop->password=$data['password'];
            foreach(['logo_image'=>'logo','og_image'=>'og'] as $field=>$directory){if($request->hasFile($field))$shop->$field=$this->image($request->file($field),$directory,$newFiles);}
            foreach($uploads as $file)$kept[]=$this->image($file,'banner',$newFiles);
            $shop->banner_images=$kept;$shop->og_title=$data['og_title']??null;$shop->og_description=$data['og_description']??null;
            $shop->use_own_pg=$ownPg;
            if($ownPg)foreach(['pg_provider','pg_merchant_id','pg_site_code','pg_client_key','pg_secret_key'] as $field)$shop->$field=$vendor->$field;
            $shop->purchase_sms_templates=$request->boolean('use_purchase_sms')?array_intersect_key($data['purchase_sms_templates']??[],array_flip(['purchase','purchase_confirmed','cancel','return'])):null;
            $shop->admin_name=$shop->use_admin?($data['admin_name']??null):null;$shop->admin_login_id=$shop->use_admin?($data['admin_login_id']??null):null;
            if($shop->use_admin && $request->filled('admin_password'))$shop->admin_password=bcrypt($data['admin_password']);
            $shop->settlement_type=(int)($data['settlement_type']??1);$shop->settlement_rate=$shop->settlement_type===1?($data['settlement_rate_percent']??0):($data['settlement_rate_amount']??0);
            $shop->save();$keep=[];
            foreach($privateRows as $row){$access=ShopChannelPrivateAccess::updateOrCreate(['shop_channel_id'=>$shop->id,'phone_normalized'=>$row['phone_normalized']],$row);$keep[]=$access->id;}
            $shop->privateAccesses()->whereNotIn('id',$keep)->delete();
            return $shop;
        });}catch(\Throwable $exception){foreach($newFiles as $file)File::delete(public_path($file));throw $exception;}
    }

    private function image($file,string $directory,array &$paths): string
    {
        $folder='uploads/shop/'.$directory;File::ensureDirectoryExists(public_path($folder));
        $path=$folder.'/'.Str::uuid().'.'.$file->extension();$file->move(public_path($folder),basename($path));$paths[]=$path;return $path;
    }
}
