<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChannelDeliveryCharge;
use App\Models\Vendor;
use App\Services\DeliveryPolicyRules;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function shippingCharges(Request $request)
    {
        session(['page' => 'shipping']);
        $filters = $request->validate(['vendor_id' => 'nullable|integer|exists:vendors,id', 'search' => 'nullable|string|max:100']);
        $charges = ChannelDeliveryCharge::with('vendor')
            ->when($filters['vendor_id'] ?? null, fn ($query, $vendorId) => $query->where('vendor_id', $vendorId))
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where('name', 'like', '%'.$term.'%'))
            ->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.shipping.shipping_charges', ['charges' => $charges, 'vendors' => Vendor::orderBy('name')->get()]);
    }

    public function store(Request $request, DeliveryPolicyRules $rules)
    {
        $data = $request->validate(['vendor_id' => 'required|integer|exists:vendors,id']);
        $charge = ChannelDeliveryCharge::create($rules->payload($request, (int) $data['vendor_id']));

        return redirect()->route('admin.shipping.edit', $charge->id)->with('success_message', '배송비 정책이 등록되었습니다.');
    }

    public function updateShippingStatus(Request $request)
    {
        $data = $request->validate(['shipping_id' => 'required|integer|exists:channel_delivery_charges,id', 'status' => 'required|boolean']);
        $charge = ChannelDeliveryCharge::findOrFail($data['shipping_id']);
        $charge->update(['status' => (int) $data['status']]);

        return response()->json(['status' => $charge->status, 'shipping_id' => $charge->id]);
    }

    public function editShippingCharges(int $id, Request $request, DeliveryPolicyRules $rules)
    {
        session(['page' => 'shipping']);
        $charge = ChannelDeliveryCharge::with('vendor')->findOrFail($id);
        if ($request->isMethod('post')) {
            $charge->update($rules->payload($request, (int) $charge->vendor_id));

            return redirect()->route('admin.shipping.edit', $charge->id)->with('success_message', '배송비 정책이 수정되었습니다.');
        }

        return view('admin.shipping.edit_shipping_charges', ['charge' => $charge,
            'shippingTypes' => DeliveryPolicyRules::shippingTypes(), 'paymentTypes' => DeliveryPolicyRules::paymentTypes()]);
    }
}
