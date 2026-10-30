<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Services\ChannelOrderManagers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChannelOrderManagerController extends Controller
{
    private function owned(int $id): Distributor
    {
        return Distributor::where('vendor_id', Auth::guard('admin')->user()->vendor_id)->findOrFail($id);
    }

    public function index(Request $request)
    {
        $data = $request->validate(['keyword' => 'nullable|string|max:100']);
        $keyword = trim($data['keyword'] ?? '');
        $vendorId = (int) Auth::guard('admin')->user()->vendor_id;
        $managers = Distributor::where('vendor_id', $vendorId)->withCount(['products' => fn ($q) => $q->where('vendor_id', $vendorId)->where('order_manager_enabled', true)])
            ->when($keyword !== '', fn ($q) => $q->where(fn ($q) => $q->where('email', 'like', '%'.$keyword.'%')->orWhere('name', 'like', '%'.$keyword.'%')->orWhere('phone', 'like', '%'.$keyword.'%')))
            ->latest('id')->paginate(20)->withQueryString();
        $activeForm = is_string(old('form_key')) && preg_match('/^(create|edit-\d+)$/', old('form_key')) ? old('form_key') : '';

        return view('channel.sub00.order_manager_list', compact('managers', 'keyword', 'activeForm'));
    }

    public function checkEmail(Request $request, ChannelOrderManagers $service)
    {
        $data = $request->validate(['manager_id' => 'nullable|integer|min:1']);
        $manager = ! empty($data['manager_id']) ? $this->owned($data['manager_id']) : null;
        $email = $service->normalizeEmail($request);
        $available = ! Distributor::where('email', $email)->when($manager, fn ($q) => $q->where('id', '!=', $manager->id))->exists();

        return response()->json(['available' => $available, 'email' => $email, 'message' => $available ? '사용 가능한 이메일입니다.' : '중복된 이메일을 가진 발주 담당이 있습니다.', 'token' => $available ? $service->emailProof(Auth::guard('admin')->user(), $email, $manager) : null]);
    }

    public function store(Request $request, ChannelOrderManagers $service)
    {
        $service->save($request, Auth::guard('admin')->user());

        return redirect()->route('channel.order.manager')->with('flash_message_success', '발주담당자가 등록되었습니다.');
    }

    public function update(Request $request, int $id, ChannelOrderManagers $service)
    {
        $service->save($request, Auth::guard('admin')->user(), $this->owned($id));

        return redirect()->route('channel.order.manager')->with('flash_message_success', '발주담당자 정보가 수정되었습니다.');
    }

    public function show(int $id)
    {
        $manager = $this->owned($id);
        $products = $manager->products()->where('vendor_id', $manager->vendor_id)->where('order_manager_enabled', true)->with('category')->orderBy('product_name')->paginate(12);

        return view('channel.sub00.inc.order_manager_details', compact('manager', 'products'));
    }

    public function portal(Request $request, int $id)
    {
        $manager = $this->owned($id);
        abort_unless($manager->canAccess(), 403);
        $request->session()->regenerate();
        $request->session()->put(['distributor_id' => $manager->id, 'distributor_name' => $manager->name, 'distributor_email' => $manager->email]);

        return redirect()->route('distributor.orders.pending')->with('flash_message_success', $manager->name.' 발주사 페이지로 연결되었습니다.');
    }
}
