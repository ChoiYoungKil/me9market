<?php
namespace App\Services;
use App\Models\ShopChannelNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Storage};
class ShopCommunity
{
    public function clean(string $html): string
    {
        $doc=new \DOMDocument();$previous=libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?><div>'.$html.'</div>',LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();libxml_use_internal_errors($previous);
        foreach(iterator_to_array($doc->getElementsByTagName('*')) as $node){
            if(in_array(strtolower($node->nodeName),['script','style','iframe','object','embed','svg','math','form','input','button','link','meta'])){$node->parentNode?->removeChild($node);continue;}
            if(!in_array(strtolower($node->nodeName),['div','p','br','b','strong','i','em','u','s','ul','ol','li','blockquote','h2','h3','h4','a','table','thead','tbody','tr','th','td'])){
                if($node->parentNode){while($node->firstChild)$node->parentNode->insertBefore($node->firstChild,$node);$node->parentNode->removeChild($node);}continue;
            }
            foreach(iterator_to_array($node->attributes) as $attribute){
                if($attribute->name!=='href'||$node->nodeName!=='a'||!preg_match('~^(https?://|mailto:)~i',$attribute->value))$node->removeAttribute($attribute->name);
            }
        }
        $out='';foreach($doc->documentElement->childNodes as $child)$out.=$doc->saveHTML($child);
        return $out;
    }
    public function save(Request $request,$shop,?ShopChannelNotice $notice=null): ShopChannelNotice
    {
        $data=$request->validate(['type'=>'required|in:notice,general','title'=>'required|string|max:500','content'=>'required|string|max:200000',
            'attachment'=>'nullable|file|max:10240|mimes:pdf,txt,csv,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,zip','delete_attachment'=>'nullable|boolean'],
            ['title.required'=>'제목을 입력해 주세요.','content.required'=>'내용을 입력해 주세요.']);
        $content=$this->clean($data['content']);
        if(trim(html_entity_decode(strip_tags($content)))==='')throw \Illuminate\Validation\ValidationException::withMessages(['content'=>'내용을 입력해 주세요.']);
        $newPath=null;$oldPath=$notice?->attachment;
        try{
            if($request->hasFile('attachment'))$newPath=$request->file('attachment')->store('channel-notices/'.$shop->id,'local');
            $notice=DB::transaction(function()use($request,$shop,$notice,$data,$content,$newPath){
                $notice=$notice??new ShopChannelNotice;$notice->shop_channel_id=$shop->id;
                $notice->type=$data['type'];$notice->title=$data['title'];$notice->content=$content;$notice->author=$shop->channel_name;$notice->status=1;
                if($request->boolean('delete_attachment')){$notice->attachment=null;$notice->attachment_name=null;}
                if($newPath){$notice->attachment=$newPath;$notice->attachment_name=basename($request->file('attachment')->getClientOriginalName());}
                $notice->save();return $notice;
            });
        }catch(\Throwable $e){if($newPath)Storage::disk('local')->delete($newPath);throw $e;}
        if($oldPath&&$oldPath!==$notice->attachment)$this->delete($oldPath);
        return $notice;
    }
    public function path(string $file): ?string
    {
        if(preg_match('~^channel-notices/[0-9]+/[A-Za-z0-9_.-]+$~',$file))return Storage::disk('local')->exists($file)?Storage::disk('local')->path($file):null;
        if(basename($file)!==$file)return null;
        foreach(['uploads/notices','uploads/channel/notices','uploads/shop_notices'] as $dir)if(is_file(public_path($dir.'/'.$file)))return public_path($dir.'/'.$file);
        return null;
    }
    public function delete(string $file): void
    {
        $path=$this->path($file);if($path)unlink($path);
    }
}
