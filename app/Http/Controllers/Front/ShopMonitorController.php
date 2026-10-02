<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\OrdersProduct;
use App\Models\ShopChannel;
use App\Services\SettlementCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ShopMonitorController extends Controller
{
    private function available(ShopChannel $shop): bool
    {
        return (int) $shop->use_admin === 1 && (int) $shop->status === 1 && $shop->closure_status !== 'approved'
            && ((int) $shop->use_period_type !== 1 || ((! $shop->start_at || now()->gte($shop->start_at)) && (! $shop->end_at || now()->lte($shop->end_at))));
    }

    private function currentShop(Request $request): ?ShopChannel
    {
        $shop = ShopChannel::find($request->session()->get('shop_monitor_id'));
        if (! $shop || ! $this->available($shop)
            || ! hash_equals(hash('sha256', (string) $shop->admin_password), (string) $request->session()->get('shop_monitor_password_version'))) {
            $request->session()->forget(['shop_monitor_id', 'shop_monitor_password_version']);
            return null;
        }
        return $shop;
    }

    public function login(Request $request)
    {
        return $this->currentShop($request) ? redirect()->route('shop.monitor.index') : view('shop.monitor.login');
    }

    public function loginSubmit(Request $request)
    {
        $data = $request->validate(['login_id' => 'required|string|max:50', 'password' => 'required|string|max:200']);
        $shop = ShopChannel::where('admin_login_id', $data['login_id'])->first();
        $validPassword = false;
        if ($shop && $this->available($shop) && filled($shop->admin_password)) {
            try {
                $validPassword = Hash::check($data['password'], $shop->admin_password);
            } catch (\RuntimeException $exception) {
                $validPassword = false;
            }
        }
        if (! $validPassword) {
            return back()->withErrors(['login_id' => '로그인 정보 또는 이용 가능 기간을 확인해 주세요.'])->withInput($request->only('login_id'));
        }
        $request->session()->regenerate();
        $request->session()->put(['shop_monitor_id' => $shop->id, 'shop_monitor_password_version' => hash('sha256', $shop->admin_password)]);
        return redirect()->route('shop.monitor.index');
    }

    public function index(Request $request, SettlementCalculator $calculator)
    {
        $shop = $this->currentShop($request);
        if (! $shop) return redirect()->route('shop.monitor.login');
        $periods = $calculator->periodOptions();
        $period = $calculator->normalizePeriod($request->input('period'));
        $from = \Carbon\Carbon::createFromFormat('!Y-m', $period)->startOfMonth();
        $query = OrdersProduct::where('shop_channel_id', $shop->id)->where('vendor_id', $shop->vendor_id)
            ->where('is_exchange_replacement', false)->whereBetween('created_at', [$from, $from->copy()->endOfMonth()]);
        $counts = (clone $query)->selectRaw('status_code, COUNT(*) as total')->groupBy('status_code')->pluck('total', 'status_code');
        $items = $query->latest('id')->paginate(30)->withQueryString();
        return view('shop.monitor.index', compact('shop', 'items', 'counts', 'period', 'periods'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['shop_monitor_id', 'shop_monitor_password_version']);
        $request->session()->regenerate();
        return redirect()->route('shop.monitor.login');
    }
}
