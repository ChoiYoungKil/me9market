<?php
namespace Tests\Feature;
use App\Models\{Admin,Vendor,Product,ShopChannel,ShopChannelProduct,ShopChannelNotice};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
class ShopCatalogAcceptanceTest extends TestCase
{
 use RefreshDatabase;
 private function fixture():array {
  $v=Vendor::create(['name'=>'검수 채널','email'=>'catalog@example.test','mobile'=>'01000000000','status'=>1,'commission'=>5]);
  $supplier=Vendor::create(['name'=>'공급사','email'=>'supplier@example.test','mobile'=>'01000000000','status'=>1,'commission'=>5]);
  $a=Admin::forceCreate(['vendor_id'=>$v->id,'name'=>'관리자','email'=>'catalog-admin@example.test','mobile'=>'01000000000','type'=>'vendor','status'=>1,'confirm'=>'Yes','password'=>bcrypt('test')]);
  $sa=Admin::forceCreate(['vendor_id'=>$supplier->id,'name'=>'공급자','email'=>'supplier-admin@example.test','mobile'=>'01000000000','type'=>'vendor','status'=>1,'confirm'=>'Yes','password'=>bcrypt('test')]);
  $s=ShopChannel::create(['vendor_id'=>$v->id,'channel_code'=>'CAT92','channel_name'=>'채널92','copyright'=>'검수','keywords'=>['검수'],'status'=>1,'is_public'=>1,'settlement_type'=>1,'settlement_rate'=>90]);
  $p=Product::forceCreate(['section_id'=>1,'category_id'=>1,'brand_id'=>1,'vendor_id'=>$supplier->id,'admin_id'=>$sa->id,'admin_type'=>'vendor','product_name'=>'공유92','product_code'=>'SH92','product_color'=>'Blue','product_price'=>5000,'product_discount'=>0,'product_weight'=>1,'status'=>1,'is_public'=>'Yes','is_partial'=>'Yes','price_constraint_enabled'=>1,'price_constraint_type'=>'range','price_min'=>6000,'price_max'=>10000]);
  $this->actingAs($a,'admin');return[$v,$a,$s,$p,$sa];
 }
 private function ajax(string $url,array $d){return $this->postJson($url,$d,['X-Requested-With'=>'XMLHttpRequest']);}
 public function test_channel_reward_price_and_company_commission_are_saved_without_using_monitor_payment_rate():void {
  [$v,$a,$s,$p]=$this->fixture();
  $d=['shop_id'=>$s->id,'product_id'=>$p->id,'selling_price'=>8000,'reward_points'=>100];
  $this->ajax('/channel/shop/product/public/store',$d)->assertJsonPath('status',true);
  $l=ShopChannelProduct::firstOrFail();$this->assertEquals(5,$l->settlement_rate_snapshot);$this->assertEquals(100,$l->reward_points);
  $this->assertEquals(2460,$l->profit);$this->assertEquals(100,app(\App\Services\OrderSettlementPolicy::class)->capture($s,$l,$p)['reward_points']);
  $v->update(['commission'=>8]);$this->assertEquals(5,$l->fresh()->settlement_rate_snapshot);
 }
 public function test_scope_price_points_and_pg_restrictions_are_enforced_by_server():void {
  [$v,$a,$s,$p]=$this->fixture();$base=['shop_id'=>$s->id,'product_id'=>$p->id,'selling_price'=>8000,'reward_points'=>100];
  $this->ajax('/channel/shop/product/public/store',array_replace($base,['selling_price'=>11000]))->assertJsonPath('status',false);
  $this->ajax('/channel/shop/product/public/store',array_replace($base,['reward_points'=>8000]))->assertJsonPath('status',false);
  $p->update(['is_public'=>'No']);$this->ajax('/channel/shop/product/public/store',$base)->assertJsonPath('status',false);
  $p->update(['is_public'=>'Yes']);$s->update(['use_own_pg'=>true]);$this->ajax('/channel/shop/product/public/store',$base)->assertJsonPath('status',false);
  $this->assertSame(0,ShopChannelProduct::count());
 }
 public function test_partner_request_cannot_sell_before_review_and_explicit_add():void {
  [$v,$a,$s,$p,$supplier]=$this->fixture();$p->update(['is_public'=>'No']);$d=['shop_id'=>$s->id,'product_id'=>$p->id,'selling_price'=>8000,'reward_points'=>100,'request_reason'=>'검수 요청'];
  $this->ajax('/channel/shop/product/partial/request',$d)->assertJsonPath('status',true);$l=ShopChannelProduct::firstOrFail();
  $this->ajax('/channel/shop/product/status/update',['shop_product_id'=>$l->id,'status'=>1])->assertJsonPath('status',false);
  $this->get('/channel/shop/product02?shop_id='.$s->id)->assertViewHas('products',fn($r)=>$r->total()===0);
  $this->actingAs($supplier,'admin');$this->ajax('/channel/shop/product/request/update',['request_id'=>$l->id,'status'=>1])->assertJsonPath('status',true);
  $this->assertSame('approved',$l->fresh()->approval_status);$this->assertEquals(0,$l->fresh()->status);$this->assertNull($l->fresh()->listed_at);
  $this->actingAs($a,'admin');$this->ajax('/channel/shop/product/partial/store',$d)->assertJsonPath('status',true);
  $this->assertEquals(1,$l->fresh()->status);$this->assertNotNull($l->fresh()->listed_at);
 }
 public function test_favorites_persist_for_current_vendor_and_search_preserves_channel():void {
  [$v,$a,$s,$p]=$this->fixture();
  $this->ajax('/channel/shop/product/favorite',['product_id'=>$p->id,'favorite'=>true])->assertOk();
  $this->get('/channel/shop/product01?shop_id='.$s->id.'&catalog_type=public&catalog_q=SH92&catalog_open=1')->assertOk()
   ->assertViewHas('catalogFavorites',fn($ids)=>in_array($p->id,$ids))->assertViewHas('catalogProducts',fn($r)=>$r->total()===1)
   ->assertSee('name="shop_id" value="'.$s->id.'"',false);
 }
 public function test_catalog_price_filters_match_range_overlap_and_allow_only_one_bound():void {
  [$v,$a,$s,$p]=$this->fixture();
  foreach(['catalog_min=9000&catalog_max=9500','catalog_max=6500','catalog_min=9000'] as $query){
   $this->get('/channel/shop/product01?shop_id='.$s->id.'&catalog_type=public&'.$query)->assertOk()
    ->assertViewHas('catalogProducts',fn($rows)=>$rows->total()===1);
  }
  $this->get('/channel/shop/product01?shop_id='.$s->id.'&catalog_type=public&catalog_min=11000')->assertOk()
   ->assertViewHas('catalogProducts',fn($rows)=>$rows->total()===0);
 }
 public function test_product_detail_response_never_contains_supplier_pg_secrets():void {
  [$v,$a,$s,$p]=$this->fixture();$p->vendor->update(['pg_secret_key'=>'secret-should-never-appear']);
  $this->getJson('/channel/product/base/detail/'.$p->id)->assertOk()->assertDontSee('secret-should-never-appear')->assertJsonMissingPath('product.vendor');
 }
 public function test_community_editor_upload_download_replace_and_owner_scope():void {
  Storage::fake('local');[$v,$a,$s,$p,$supplier]=$this->fixture();
  $d=['shop_id'=>$s->id,'type'=>'notice','title'=>'검수 공지','content'=>'<p><strong>검수</strong><script>alert(1)</script><a href="javascript:alert(1)">링크</a></p>','attachment'=>UploadedFile::fake()->createWithContent('review.txt','original text')];
  $this->post('/channel/shop/community/register',$d)->assertRedirect();$n=ShopChannelNotice::firstOrFail();
  $this->assertStringNotContainsString('<script',$n->content);$this->assertStringNotContainsString('javascript:',$n->content);$this->assertStringContainsString('<strong>',$n->content);
  $old=$n->attachment;Storage::disk('local')->assertExists($old);
  $this->get('/channel/shop/community/attachment/'.$n->id)->assertOk()->assertDownload('review.txt');
  $this->post('/channel/shop/community/update/'.$n->id,array_replace($d,['title'=>'','attachment'=>UploadedFile::fake()->createWithContent('bad.txt','new')]))->assertSessionHasErrors('title');
  Storage::disk('local')->assertExists($old);
  $this->post('/channel/shop/community/update/'.$n->id,array_replace($d,['attachment'=>UploadedFile::fake()->createWithContent('replacement.txt','replacement')]))->assertRedirect();
  Storage::disk('local')->assertMissing($old);Storage::disk('local')->assertExists($n->fresh()->attachment);
  $this->actingAs($supplier,'admin')->get('/channel/shop/community/attachment/'.$n->id)->assertNotFound();
  $this->get('/channel/shop/community/register?shop_id='.$s->id)->assertRedirect(route('channel.shop_list'));
 }
 public function test_unsafe_attachment_and_empty_rich_text_do_not_create_posts():void {
  Storage::fake('local');[$v,$a,$s]=$this->fixture();$d=['shop_id'=>$s->id,'type'=>'notice','title'=>'검수','content'=>'<p>내용</p>'];
  $this->post('/channel/shop/community/register',$d+['attachment'=>UploadedFile::fake()->createWithContent('bad.php','<?php phpinfo();')])->assertSessionHasErrors('attachment');
  $this->post('/channel/shop/community/register',array_replace($d,['content'=>'<p><br></p>']))->assertSessionHasErrors('content');
  $this->assertSame(0,ShopChannelNotice::count());
 }
}
