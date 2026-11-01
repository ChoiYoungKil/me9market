<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\ChannelOrderController;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Support\OrderItemStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AdminOrderWorkspaceController extends Controller
{
    private function order(int $id): Order
    {
        abort_unless(in_array(Auth::guard('admin')->user()?->type, ['admin', 'superadmin'], true), 403);

        return Order::with(['orders_products.product.attributes', 'orders_products.shopChannel', 'orders_products.claims', 'orders_products.exchangeReplacement', 'statusLogs', 'payments'])->findOrFail($id);
    }

    public function show(int $id)
    {
        Session::put('page', 'orders');
        $order = $this->order($id);
        $tab = request()->query('tab', 'info');
        abort_unless(in_array($tab, ['info', 'products', 'claims', 'history'], true), 404);

        return view('admin.workspace.order', compact('order', 'tab'));
    }

    public function panel(int $id, string $tab)
    {
        abort_unless(in_array($tab, ['info', 'products', 'claims', 'history'], true), 404);
        $order = $this->order($id);
        if (! request()->ajax()) {
            return redirect(url('admin/orders/'.$id.'?tab='.$tab));
        }

return view('admin.workspace.order_panel', compact('order', 'tab'));
    }

    public function requestForm(int $id, int $itemId, string $operation)
    {
        $order = $this->order($id);
        $item = $order->orders_products->firstWhere('id', $itemId);
        abort_unless($item && in_array($operation, ['cancel', 'return', 'exchange'], true), 404);
        abort_unless(OrderItemStatus::customerActionAllowed($operation, $item->normalized_status), 422);

        return view('admin.workspace.claim_request', compact('order', 'item', 'operation'));
    }

    public function process(Request $request, int $id, string $operation = 'action')
    {
        $this->order($id);
        $data = $request->validate(['item_ids' => 'required|array|min:1|max:100', 'item_ids.*' => 'required|integer|distinct|min:1']);
        $items = OrdersProduct::where('order_id', $id)->whereIn('id', $data['item_ids'])->get();
        abort_unless($items->count() === count($data['item_ids']), 404);
        if ($items->pluck('vendor_id')->unique()->count() !== 1) {
            throw ValidationException::withMessages(['item_ids' => '같은 공급 판매자의 상품끼리 처리해 주세요.']);
        }
        $request->merge(['order_id' => $id]);

        return app(ChannelOrderController::class)->handleForAdministrator($request,(int) $items->first()->vendor_id,$operation);
    }
}
