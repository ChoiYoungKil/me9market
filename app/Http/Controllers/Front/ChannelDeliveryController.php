<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ChannelDeliveryCharge;
use App\Models\Product;
use App\Services\DeliveryPolicyCalculator;
use App\Services\DeliveryPolicyRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChannelDeliveryController extends Controller
{
    private function vendorId(): int
    {
        $id = (int) Auth::guard('admin')->user()->vendor_id;
        abort_unless($id > 0, 403);

        return $id;
    }

    private function owned(int $id, bool $lock = false): ChannelDeliveryCharge
    {
        return ChannelDeliveryCharge::where('vendor_id', $this->vendorId())->when($lock, fn ($q) => $q->lockForUpdate())->findOrFail($id);
    }

    private function linkedProducts(ChannelDeliveryCharge $charge)
    {
        return Product::where('vendor_id', $charge->vendor_id)->where(fn ($q) => $q->where('shipping_policy_id', $charge->id)
            ->orWhere(fn ($q) => $q->whereNull('shipping_policy_id')->where('shipping_policy_name', $charge->name)));
    }

    public function index(Request $request)
    {
        $filters = $request->validate(['keyword' => 'nullable|string|max:100']);
        $keyword = trim($filters['keyword'] ?? '');
        $charges = ChannelDeliveryCharge::where('vendor_id', $this->vendorId())
            ->addSelect(['linked_product_count' => Product::selectRaw('count(*)')->whereColumn('products.vendor_id', 'channel_delivery_charges.vendor_id')
                ->where(fn ($q) => $q->whereColumn('shipping_policy_id', 'channel_delivery_charges.id')
                    ->orWhere(fn ($q) => $q->whereNull('shipping_policy_id')->whereColumn('shipping_policy_name', 'channel_delivery_charges.name')))])
            ->when($keyword !== '', fn ($q) => $q->where('name', 'like', '%'.$keyword.'%'))
            ->latest('id')->paginate(20)->withQueryString();
        $activeForm = is_string(old('form_key')) && preg_match('/^(create|edit-\d+)$/', old('form_key')) ? old('form_key') : '';

        return view('channel.sub00.delivery_charge_list', compact('charges', 'keyword', 'activeForm') + [
            'shippingTypes' => DeliveryPolicyRules::shippingTypes(), 'paymentTypes' => DeliveryPolicyRules::paymentTypes(),
        ]);
    }

    private function payload(Request $request, DeliveryPolicyRules $rules): array
    {
        return $rules->payload($request, $this->vendorId());
    }

    public function store(Request $request, DeliveryPolicyRules $rules)
    {
        ChannelDeliveryCharge::create($this->payload($request, $rules));

        return redirect()->route('channel.delivery.list')->with('flash_message_success', '배송비가 등록되었습니다.');
    }

    public function update(Request $request, int $id, DeliveryPolicyRules $rules)
    {
        DB::transaction(function () use ($request, $id, $rules) {
            $charge = $this->owned($id, true);
            $data = $this->payload($request, $rules);
            // Resolve older name-only associations before renaming the policy.
            if ($charge->name !== $data['name']) {
                $hasLegacy = Product::where('vendor_id', $charge->vendor_id)->whereNull('shipping_policy_id')->where('shipping_policy_name', $charge->name)->exists();
                if ($hasLegacy && ChannelDeliveryCharge::where('vendor_id', $charge->vendor_id)->where('name', $charge->name)->where('id', '!=', $charge->id)->exists()) {
                    throw ValidationException::withMessages(['name' => '같은 명칭의 배송비 정책이 여러 개 있습니다. 상품에서 배송비 정책을 다시 선택한 후 명칭을 수정해 주세요.']);
                }
                $this->linkedProducts($charge)->update(['shipping_policy_id' => $charge->id, 'shipping_policy_name' => $data['name']]);
            }
            $charge->update($data);
        });

        return redirect()->route('channel.delivery.list')->with('flash_message_success', '배송비가 수정되었습니다.');
    }

    public function show(int $id)
    {
        $charge = $this->owned($id);
        $charge->linked_product_count = $this->linkedProducts($charge)->count();

        return view('channel.sub00.inc.channel_delivery_details', compact('charge') + ['shippingTypes' => DeliveryPolicyRules::shippingTypes(), 'paymentTypes' => DeliveryPolicyRules::paymentTypes()]);
    }

    public function copy(int $id)
    {
        DB::transaction(function () use ($id) {
            $source = $this->owned($id, true);
            $base = Str::limit($source->name, 70, '');
            $name = $base.' (복사)';
            for ($n = 2; ChannelDeliveryCharge::where('vendor_id', $source->vendor_id)->where('name', $name)->exists(); $n++) {
                $name = $base.' (복사 '.$n.')';
            }
            $copy = $source->replicate();
            $copy->name = $name;
            $copy->product_count = 0;
            $copy->save();
        });

        return redirect()->route('channel.delivery.list')->with('flash_message_success', '배송비 설정이 복사되었습니다. 연결 상품은 원본에 유지됩니다.');
    }

    public function destroy(int $id)
    {
        DB::transaction(function () use ($id) {
            $charge = $this->owned($id, true);
            if ($this->linkedProducts($charge)->exists()) {
                throw ValidationException::withMessages(['shipping' => '상품에서 사용 중인 배송비 정책은 삭제할 수 없습니다.']);
            }
            $charge->delete();
        });

        return redirect()->route('channel.delivery.list')->with('flash_message_success', '배송비가 삭제되었습니다.');
    }

    public function preview(Request $request, DeliveryPolicyRules $rules, DeliveryPolicyCalculator $calculator)
    {
        $sample = $request->validate([
            'preview_amount' => 'required|integer|min:0|max:999999999', 'preview_quantity' => 'required|integer|min:1|max:999999',
            'preview_postcode' => ['nullable', 'regex:/^[0-9]{5}$/'], 'preview_address' => 'nullable|string|max:255',
        ]);
        $charge = new ChannelDeliveryCharge($rules->payload($request, $this->vendorId()));
        $cod = $charge->payment_type === 'cod';
        $charge->payment_type = 'prepaid';
        $destination = array_filter(['pincode' => $sample['preview_postcode'] ?? '', 'address' => $sample['preview_address'] ?? ''], fn ($v) => $v !== '');
        $fee = $calculator->fee($charge, $sample['preview_amount'], $sample['preview_quantity'], $destination);

        return response()->json(['fee' => $fee, 'pay_now' => $cod ? 0 : $fee, 'payment_type' => $cod ? 'cod' : 'prepaid',
            'message' => ($cod ? '착불 예상 배송비 ' : '선결제 배송비 ').number_format($fee).'원'.($cod ? ' · 주문 시 결제 0원' : '')]);
    }
}
