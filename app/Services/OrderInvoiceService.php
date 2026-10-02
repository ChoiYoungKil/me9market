<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\File;

class OrderInvoiceService
{
    public function data(Order $order): array
    {
        // Controllers must authorize the order and scope its loaded items first.
        $order->loadMissing('orders_products');
        $calculator = app(SettlementCalculator::class);
        $items = $order->orders_products->map(fn ($item) => [
            'item' => $item, 'amounts' => $calculator->paymentAmounts($item),
        ]);
        $totals = [];
        foreach (['paid_line_total', 'shipping', 'points', 'coupon', 'cash'] as $key) {
            $totals[$key] = $items->sum(fn ($row) => $row['amounts'][$key]);
        }
        return compact('order', 'items', 'totals');
    }

    public function download(Order $order)
    {
        $options = new \Dompdf\Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isPhpEnabled', false);
        $options->set('chroot', public_path());
        $options->set('fontDir', storage_path('fonts'));
        $options->set('fontCache', storage_path('fonts'));
        File::ensureDirectoryExists(storage_path('fonts'));
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml(view('admin.orders.order_invoice', $this->data($order) + ['pdf' => true])->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Me9-order-'.(int) $order->id.'.pdf"',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
