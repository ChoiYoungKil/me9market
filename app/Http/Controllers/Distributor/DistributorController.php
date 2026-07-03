<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\OrdersProduct;
use App\Services\ShopChannelRuntime;
use App\Support\OrderItemStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class DistributorController extends Controller
{
    private function ensureRuntime(): void
    {
        app(ShopChannelRuntime::class)->ensureDemoData();
    }

    private function currentDistributor(): ?Distributor
    {
        $id = Session::get('distributor_id');

        return $id ? Distributor::find($id) : null;
    }

    public function login()
    {
        $this->ensureRuntime();

        if ($this->currentDistributor()) {
            return redirect()->route('distributor.orders.pending');
        }

        return view('distributor.login');
    }

    public function loginSubmit(Request $request)
    {
        $this->ensureRuntime();

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $distributor = Distributor::where('email', $request->email)->first();

        if (!$distributor || !Hash::check($request->password, $distributor->password) || (int) $distributor->status !== 1) {
            return back()
                ->withInput($request->only('email'))
                ->with('flash_message_error', '발주사 계정 정보가 올바르지 않습니다.');
        }

        Session::put('distributor_id', $distributor->id);
        Session::put('distributor_name', $distributor->name);
        Session::put('distributor_email', $distributor->email);

        return redirect()->route('distributor.orders.pending')->with('flash_message_success', '발주사 로그인 성공!');
    }

    public function logout()
    {
        Session::forget(['distributor_id', 'distributor_name', 'distributor_email']);

        return redirect()->route('distributor.login')->with('flash_message_success', '로그아웃 되었습니다.');
    }

    public function ordersPending()
    {
        $this->ensureRuntime();
        $distributor = $this->currentDistributor();

        if (!$distributor) {
            return redirect()->route('distributor.login');
        }

        $orders = $this->orderItemQuery($distributor)
            ->where(function ($query) {
                $query->whereIn('status_code', [OrderItemStatus::PAID, OrderItemStatus::READY_TO_SHIP])
                    ->orWhereIn('item_status', ['Payment Captured', 'New', 'In Process', '결제완료', '배송대기']);
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (OrdersProduct $item) => $this->orderItemRow($item));

        $stats = $this->stats($distributor);

        return view('distributor.orders_pending', compact('orders', 'stats'));
    }

    public function ordersCompleted()
    {
        $this->ensureRuntime();
        $distributor = $this->currentDistributor();

        if (!$distributor) {
            return redirect()->route('distributor.login');
        }

        $orders = $this->orderItemQuery($distributor)
            ->where(function ($query) {
                $query->whereIn('status_code', [OrderItemStatus::SHIPPING, OrderItemStatus::DELIVERED, OrderItemStatus::CONFIRMED])
                    ->orWhereNotNull('tracking_number');
            })
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (OrdersProduct $item) => $this->orderItemRow($item));

        return view('distributor.orders_completed', compact('orders'));
    }

    public function orderDetails($id)
    {
        $this->ensureRuntime();
        $distributor = $this->currentDistributor();

        if (!$distributor) {
            return redirect()->route('distributor.login');
        }

        $item = $this->orderItemQuery($distributor)->findOrFail($id);
        $order = $this->orderItemRow($item);

        return view('distributor.order_details', compact('order'));
    }

    public function updateOrder(Request $request, $id)
    {
        $this->ensureRuntime();
        $distributor = $this->currentDistributor();

        if (!$distributor) {
            return redirect()->route('distributor.login');
        }

        $data = $request->validate([
            'receiver' => 'nullable|string|max:100',
            'zipcode' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'courier' => 'nullable|string|max:100',
            'tracking_no' => 'nullable|string|max:100',
            'status_code' => 'nullable|string|in:' . implode(',', array_keys(OrderItemStatus::labels())),
        ]);

        $item = $this->orderItemQuery($distributor)->findOrFail($id);
        $order = $item->order;

        if ($order) {
            $order->name = $data['receiver'] ?? $order->name;
            $order->pincode = $data['zipcode'] ?? $order->pincode;
            $order->address = $data['address'] ?? $order->address;
            $order->city = $data['city'] ?? $order->city;
            $order->state = $data['state'] ?? $order->state;
            $order->save();
        }

        $item->courier_name = $data['courier'] ?? $item->courier_name;
        $item->tracking_number = $data['tracking_no'] ?? $item->tracking_number;

        if (!empty($data['status_code'])) {
            $item->setStatus($data['status_code']);
        } elseif (!empty($item->courier_name) && !empty($item->tracking_number)) {
            $item->setStatus(OrderItemStatus::SHIPPING);
        }

        $this->applyStatusTimestamps($item);
        $item->save();

        return redirect()
            ->route('distributor.order.details', $id)
            ->with('flash_message_success', '발주 배송 정보가 저장되었습니다.');
    }

    public function uploadInvoice(Request $request)
    {
        $this->ensureRuntime();
        $distributor = $this->currentDistributor();

        if (!$distributor) {
            return redirect()->route('distributor.login');
        }

        $request->validate([
            'invoice_file' => 'required|file|max:5120',
        ]);

        $rows = $this->parseInvoiceRows(
            $request->file('invoice_file')->getRealPath(),
            strtolower($request->file('invoice_file')->getClientOriginalExtension())
        );

        $updated = 0;
        foreach ($rows as $row) {
            $itemId = $this->rowValue($row, ['order_item_id', 'item_id', 'id', '주문상품ID', '품목ID']);
            $courier = $this->rowValue($row, ['courier_name', 'courier', '택배사']);
            $trackingNo = $this->rowValue($row, ['tracking_number', 'tracking_no', 'invoice_no', '송장번호', '운송장번호']);

            if (!$itemId || !$courier || !$trackingNo) {
                continue;
            }

            $item = OrdersProduct::where('distributor_id', $distributor->id)->find($itemId);
            if (!$item) {
                continue;
            }

            $item->courier_name = $courier;
            $item->tracking_number = $trackingNo;
            $item->setStatus(OrderItemStatus::SHIPPING);
            $this->applyStatusTimestamps($item);
            $item->save();
            $updated++;
        }

        if ($updated === 0) {
            return back()->with('flash_message_error', '적용할 수 있는 송장 데이터가 없습니다. order_item_id, courier_name, tracking_number 컬럼을 확인해 주세요.');
        }

        return redirect()
            ->route('distributor.orders.completed')
            ->with('flash_message_success', "{$updated}건의 송장이 등록되어 발주완료 목록으로 이동했습니다.");
    }

    private function orderItemQuery(Distributor $distributor)
    {
        return OrdersProduct::with(['order', 'shopChannel', 'product'])
            ->where('distributor_id', $distributor->id);
    }

    private function stats(Distributor $distributor): array
    {
        $base = OrdersProduct::where('distributor_id', $distributor->id);

        return [
            'pending' => (clone $base)->whereIn('status_code', [OrderItemStatus::PAID, OrderItemStatus::READY_TO_SHIP])->count(),
            'shipping' => (clone $base)->where('status_code', OrderItemStatus::SHIPPING)->count(),
            'delivered' => (clone $base)->whereIn('status_code', [OrderItemStatus::DELIVERED, OrderItemStatus::CONFIRMED])->count(),
        ];
    }

    private function orderItemRow(OrdersProduct $item): array
    {
        $order = $item->order;
        $option = collect([$item->product_color, $item->product_size])
            ->filter(fn ($value) => filled($value) && $value !== '-')
            ->implode(' / ');

        return [
            'id' => $item->id,
            'order_id' => 'Me9-Shop-' . str_pad((string) $item->order_id, 7, '0', STR_PAD_LEFT),
            'channel_name' => $item->shopChannel?->channel_name ?? 'Me9 Shop',
            'product_code' => $item->product_code,
            'product_name' => $item->product_name,
            'option' => $option ?: '기본옵션',
            'quantity' => $item->product_qty,
            'status' => $item->status_label,
            'status_code' => $item->normalized_status,
            'receiver' => $order?->name ?? '-',
            'address' => $order?->address ?? '',
            'full_address' => trim(($order?->pincode ? $order->pincode . ' ' : '') . ($order?->address ?? '')),
            'zipcode' => $order?->pincode ?? '',
            'city' => $order?->city ?? '',
            'state' => $order?->state ?? '',
            'request_date' => optional($item->created_at)->format('Y-m-d H:i:s') ?? '-',
            'shipped_date' => optional($item->shipped_at ?: $item->updated_at)->format('Y-m-d H:i:s') ?? '-',
            'courier' => $item->courier_name ?: '-',
            'tracking_no' => $item->tracking_number ?: '-',
            'can_edit_shipping' => in_array($item->normalized_status, [OrderItemStatus::PAID, OrderItemStatus::READY_TO_SHIP], true),
            'status_options' => OrderItemStatus::labels(),
        ];
    }

    private function applyStatusTimestamps(OrdersProduct $item): void
    {
        if ($item->normalized_status === OrderItemStatus::SHIPPING && !$item->shipped_at) {
            $item->shipped_at = now();
        }

        if ($item->normalized_status === OrderItemStatus::DELIVERED && !$item->delivered_at) {
            $item->delivered_at = now();
        }

        if ($item->normalized_status === OrderItemStatus::CONFIRMED && !$item->confirmed_at) {
            $item->confirmed_at = now();
        }
    }

    private function parseInvoiceRows(string $path, string $extension): array
    {
        if ($extension === 'xlsx') {
            return $this->parseXlsxRows($path);
        }

        return $this->parseCsvRows($path);
    }

    private function parseCsvRows(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return [];
        }

        $headers = [];
        $rows = [];
        while (($line = fgetcsv($handle)) !== false) {
            if ($line === [null] || $line === false) {
                continue;
            }

            if (!$headers) {
                $headers = array_map(fn ($value) => trim((string) $value), $line);
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = trim((string) ($line[$index] ?? ''));
            }
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function parseXlsxRows(string $path): array
    {
        if (!class_exists(\ZipArchive::class)) {
            return [];
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $sharedStrings = $this->xlsxSharedStrings($zip);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheet) {
            return [];
        }

        $xml = simplexml_load_string($sheet);
        if (!$xml || !isset($xml->sheetData)) {
            return [];
        }

        $headers = [];
        $rows = [];
        foreach ($xml->sheetData->row as $rowXml) {
            $cells = [];
            foreach ($rowXml->c as $cellXml) {
                $reference = (string) $cellXml['r'];
                $index = $this->xlsxColumnIndex($reference);
                $cells[$index] = $this->xlsxCellValue($cellXml, $sharedStrings);
            }

            if (!$headers) {
                ksort($cells);
                $headers = array_values($cells);
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = trim((string) ($cells[$index] ?? ''));
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function xlsxSharedStrings(\ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if (!$xml) {
            return [];
        }

        $strings = [];
        $shared = simplexml_load_string($xml);
        if (!$shared) {
            return [];
        }

        foreach ($shared->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            $text = '';
            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }
            $strings[] = $text;
        }

        return $strings;
    }

    private function xlsxCellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];
        if ($type === 's') {
            return $sharedStrings[(int) $cell->v] ?? '';
        }

        if ($type === 'inlineStr') {
            return (string) ($cell->is->t ?? '');
        }

        return (string) ($cell->v ?? '');
    }

    private function xlsxColumnIndex(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference));
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function rowValue(array $row, array $aliases): ?string
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalized[$this->normalizeHeader($key)] = trim((string) $value);
        }

        foreach ($aliases as $alias) {
            $key = $this->normalizeHeader($alias);
            if (array_key_exists($key, $normalized) && $normalized[$key] !== '') {
                return $normalized[$key];
            }
        }

        return null;
    }

    private function normalizeHeader(string $header): string
    {
        return strtolower(preg_replace('/[\s_\-]+/', '', trim($header)));
    }
}
