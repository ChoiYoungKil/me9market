<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DeliveryPolicyRules
{
    public static function shippingTypes(): array
    {
        return ['free' => '무료배송', 'conditional' => '무료배송(조건부)', 'fixed' => '고정 배송비', 'variable' => '변동 배송비', 'tiered' => '구간별 배송비'];
    }

    public static function paymentTypes(): array
    {
        return ['prepaid' => '선결제', 'cod' => '착불'];
    }

    public function payload(Request $request, int $vendorId): array
    {
        $data = $request->validate([
            'status' => 'required|in:0,1',
            'name' => 'required|string|max:100',
            'courier' => 'nullable|string|max:100',
            'shipping_type' => 'required|in:free,conditional,fixed,variable,tiered',
            'payment_type' => 'required|in:prepaid,cod',
            'base_fee' => 'nullable|integer|min:0|max:99999999',
            'free_order_amount' => 'nullable|integer|min:0|max:99999999',
            'free_order_quantity' => 'nullable|integer|min:0|max:999999',
            'fixed_fee' => 'nullable|integer|min:0|max:99999999',
            'memo' => 'nullable|string|max:1000',
        ]);

        $data['calculation_rules'] = $this->validate($request);
        $data['vendor_id'] = $vendorId;
        $data['base_fee'] = (int) ($data['base_fee'] ?? 0);
        $data['free_order_amount'] = $data['free_order_amount'] ?? null;
        $data['free_order_quantity'] = $data['free_order_quantity'] ?? null;
        $data['fixed_fee'] = $data['fixed_fee'] ?? null;

        if ($data['shipping_type'] === 'free') {
            $data['base_fee'] = 0;
            $data['free_order_amount'] = null;
            $data['free_order_quantity'] = null;
            $data['fixed_fee'] = null;
        }

        if ($data['shipping_type'] === 'fixed') {
            $data['fixed_fee'] = (int) ($data['fixed_fee'] ?? $data['base_fee']);
            $data['base_fee'] = $data['fixed_fee'];
            $data['free_order_amount'] = null;
            $data['free_order_quantity'] = null;
        }

        return $data;
    }

    public function validate(Request $request): array
    {
        $request->validate(['calculation_rules' => 'nullable|array', 'jeju_postcodes_text' => 'nullable|string|max:100000', 'remote_postcodes_text' => 'nullable|string|max:100000']);
        foreach (['jeju', 'remote'] as $region) {
            if ($request->has($region.'_postcodes_text')) {
                $request->merge(['calculation_rules' => array_replace((array) $request->input('calculation_rules', []), [
                    $region.'_postcodes' => preg_split('/[\\s,]+/', trim((string) $request->input($region.'_postcodes_text')), -1, PREG_SPLIT_NO_EMPTY),
                ])]);
            }
        }
        if ($request->input('shipping_type') !== 'tiered') {
            $rules = (array) $request->input('calculation_rules', []);
            unset($rules['tiers']);
            $request->merge(['calculation_rules' => $rules]);
        }
        $data = $request->validate([
            'calculation_rules' => 'nullable|array',
            'calculation_rules.basis' => 'required_if:shipping_type,variable,tiered|in:amount,quantity',
            'calculation_rules.repeat_every' => 'required_if:shipping_type,variable|integer|min:1|max:99999999',
            'calculation_rules.repeat_fee' => 'required_if:shipping_type,variable|integer|min:0|max:99999999',
            'calculation_rules.start_at_one' => 'nullable|boolean',
            'calculation_rules.repeat_region' => 'nullable|boolean',
            'calculation_rules.tiers' => 'required_if:shipping_type,tiered|array|max:50',
            'calculation_rules.tiers.*.min' => 'required|integer|min:0|max:99999999',
            'calculation_rules.tiers.*.max' => 'nullable|integer|min:1|max:99999999',
            'calculation_rules.tiers.*.fee' => 'required|integer|min:0|max:99999999',
            'calculation_rules.region_mode' => 'nullable|in:none,two,three',
            'calculation_rules.jeju_fee' => 'nullable|integer|min:0|max:99999999',
            'calculation_rules.remote_fee' => 'nullable|integer|min:0|max:99999999',
            'calculation_rules.mainland_fee' => 'nullable|integer|min:0|max:99999999',
            'calculation_rules.block_jeju' => 'nullable|boolean',
            'calculation_rules.block_remote' => 'nullable|boolean',
            'calculation_rules.jeju_postcodes' => 'nullable|array|max:5000',
            'calculation_rules.remote_postcodes' => 'nullable|array|max:5000',
            'calculation_rules.jeju_postcodes.*' => ['required', 'regex:/^[0-9]{5}(-[0-9]{5})?$/'],
            'calculation_rules.remote_postcodes.*' => ['required', 'regex:/^[0-9]{5}(-[0-9]{5})?$/'],
        ]);
        $rules = $data['calculation_rules'] ?? [];
        if ($request->input('shipping_type') === 'tiered') {
            $tiers = $rules['tiers'];
            $previousMax = 0;
            foreach ($tiers as $index => &$tier) {
                $tier = ['min' => (int) $tier['min'], 'max' => filled($tier['max'] ?? null) ? (int) $tier['max'] : null, 'fee' => (int) $tier['fee']];
                if ($tier['min'] !== $previousMax || ($tier['max'] !== null && $tier['max'] <= $tier['min'])
                    || ($tier['max'] === null && $index !== count($tiers) - 1)) {
                    throw ValidationException::withMessages(['calculation_rules.tiers' => '구간은 0부터 빈틈이나 중복 없이 이어져야 합니다. 마지막 구간의 상한은 비워 주세요.']);
                }
                $previousMax = $tier['max'];
            }
            unset($tier);
            if ($previousMax !== null) {
                throw ValidationException::withMessages(['calculation_rules.tiers' => '마지막 구간의 상한은 비워 주세요.']);
            }
            $rules['tiers'] = $tiers;
        }
        foreach (['jeju', 'remote'] as $region) {
            foreach ($rules[$region.'_postcodes'] ?? [] as $range) {
                $bounds = explode('-', $range);
                if (isset($bounds[1]) && $bounds[0] > $bounds[1]) {
                    throw ValidationException::withMessages(['calculation_rules.'.$region.'_postcodes' => '우편번호 범위를 확인해 주세요.']);
                }
            }
        }
        if ((($rules['region_mode'] ?? 'none') !== 'none' || ($rules['block_remote'] ?? false)) && empty($rules['remote_postcodes'])) {
            throw ValidationException::withMessages(['calculation_rules.remote_postcodes' => '택배사 기준 도서산간 우편번호를 등록해 주세요.']);
        }
        if ((($rules['region_mode'] ?? 'none') !== 'none' || ($rules['block_jeju'] ?? false)) && empty($rules['jeju_postcodes'])) {
            throw ValidationException::withMessages(['calculation_rules.jeju_postcodes' => '택배사 기준 제주 우편번호를 등록해 주세요.']);
        }

        return $rules;
    }
}
