<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ShopCancelRefundPolicy;
use App\Services\RefundPolicyContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChannelRefundPolicyController extends Controller
{
    private function vendorId(): int
    {
        $id = (int) Auth::guard('admin')->user()->vendor_id;
        abort_unless($id > 0, 403);

        return $id;
    }

    private function owned(int $id, bool $lock = false): ShopCancelRefundPolicy
    {
        return ShopCancelRefundPolicy::where('vendor_id', $this->vendorId())->when($lock, fn ($q) => $q->lockForUpdate())->findOrFail($id);
    }

    private function linkedProducts(ShopCancelRefundPolicy $policy)
    {
        return Product::where('vendor_id', $policy->vendor_id)->where('cancel_refund_policy_id', $policy->id);
    }

    private function payload(Request $request): array
    {
        $data = $request->validate([
            'type' => 'required|string|in:default,custom',
            'status' => 'required|string|in:active,inactive',
            'name' => 'required|string|max:255',
            'content' => 'nullable|string|max:15000',
        ], [
            'type.required' => '설정구분을 선택해 주세요.', 'type.in' => '설정구분을 다시 선택해 주세요.',
            'status.required' => '상태를 선택해 주세요.', 'status.in' => '상태를 다시 선택해 주세요.',
            'name.required' => '취소/환불안내 명칭을 입력해 주세요.', 'name.max' => '명칭은 255자 이내로 입력해 주세요.',
            'content.max' => '안내 내용은 15,000자 이내로 입력해 주세요.',
        ]);

        return array_replace($data, ['vendor_id' => $this->vendorId(), 'content' => app(RefundPolicyContent::class)->forStorage($data['content'] ?? '')]);
    }

    public function index(Request $request)
    {
        $filters = $request->validate(['keyword' => 'nullable|string|max:100']);
        $keyword = trim($filters['keyword'] ?? '');
        $policies = ShopCancelRefundPolicy::where('vendor_id', $this->vendorId())
            ->addSelect(['linked_product_count' => Product::selectRaw('count(*)')->whereColumn('products.vendor_id', 'shop_cancel_refund_policies.vendor_id')->whereColumn('cancel_refund_policy_id', 'shop_cancel_refund_policies.id')])
            ->when($keyword !== '', fn ($q) => $q->where('name', 'like', '%'.$keyword.'%'))->latest('id')->paginate(20)->withQueryString();
        $activeForm = is_string(old('form_key')) && preg_match('/^(create|edit-\d+)$/', old('form_key')) ? old('form_key') : '';

        return view('channel.sub00.cancel_refund_list', compact('policies', 'keyword', 'activeForm'));
    }

    private function reply(Request $request, string $message, ?ShopCancelRefundPolicy $policy = null)
    {
        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => $message, 'policy' => $policy]);
        }

        return redirect()->route('channel.refund.list')->with('flash_message_success', $message);
    }

    public function store(Request $request)
    {
        $policy = ShopCancelRefundPolicy::create($this->payload($request) + ['product_count' => 0]);

        return $this->reply($request, '취소/환불안내가 등록되었습니다.', $policy);
    }

    public function show(Request $request, int $id)
    {
        $policy = $this->owned($id);
        $policy->linked_product_count = $this->linkedProducts($policy)->count();
        if ($request->expectsJson()) {
            $policy->product_count = $policy->linked_product_count;

            return response()->json(['status' => 'success', 'policy' => $policy]);
        }

        return view('channel.sub00.inc.refund_policy_details', compact('policy'));
    }

    public function update(Request $request, int $id)
    {
        $policy = $this->owned($id);
        $policy->update($this->payload($request));

        return $this->reply($request, '취소/환불안내가 수정되었습니다.', $policy);
    }

    public function copy(Request $request, int $id)
    {
        $copy = DB::transaction(function () use ($id) {
            $source = $this->owned($id, true);
            $base = Str::limit($source->name, 220, '');
            $name = $base.' (복사본)';
            for ($n = 2; ShopCancelRefundPolicy::where('vendor_id', $source->vendor_id)->where('name', $name)->exists(); $n++) {
                $name = $base.' (복사본 '.$n.')';
            }
            $copy = $source->replicate();
            $copy->fill(['name' => $name, 'status' => 'inactive', 'product_count' => 0]);
            $copy->save();

            return $copy;
        });

        $message = '취소/환불안내가 복사되었습니다.';

        return $this->reply($request, $message.($request->expectsJson() ? '' : ' 복사본은 중지 상태로 등록됩니다.'), $copy);
    }

    public function destroy(Request $request, int $id)
    {
        DB::transaction(function () use ($id) {
            $policy = $this->owned($id, true);
            if ($this->linkedProducts($policy)->exists()) {
                throw ValidationException::withMessages(['policy' => '상품에서 사용 중인 취소/환불안내는 삭제할 수 없습니다.']);
            }
            $policy->delete();
        });

        return $this->reply($request, '취소/환불안내가 삭제되었습니다.');
    }
}
