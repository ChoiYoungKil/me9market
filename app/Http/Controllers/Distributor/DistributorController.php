<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\OrdersLog;
use App\Models\OrdersProduct;
use App\Services\DistributorInvoiceFile;
use App\Services\OrderFulfillmentService;
use App\Support\OrderItemStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DistributorController extends Controller
{
    private function currentDistributor(): ?Distributor
    {
        $id = Session::get('distributor_id');

        $distributor = $id ? Distributor::where('status', 1)->find($id) : null;
        if ($distributor && ! $distributor->canAccess()) {
            $distributor = null;
        }
        if (! $distributor && $id) {
            Session::forget(['distributor_id', 'distributor_name', 'distributor_email']);
        }

        return $distributor;
    }

    public function login()
    {
        if ($this->currentDistributor()) {
            return redirect()->route('distributor.orders.pending');
        }

        return view('distributor.login');
    }

    public function loginSubmit(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|max:255',
            'name' => 'nullable|string|max:100',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->email).'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => '로그인 시도가 너무 많습니다. '.$seconds.'초 후 다시 시도해 주세요.',
            ]);
        }

        $distributor = Distributor::where('email', $request->email)->first();

        if (! $distributor || ! Hash::check($request->password, $distributor->password) || ($request->filled('name') && $request->name !== $distributor->name) || ! $distributor->canAccess()) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withInput($request->only('email', 'name'))
                ->with('flash_message_error', '발주사 계정 정보가 올바르지 않습니다.');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();
        Session::put('distributor_id', $distributor->id);
        Session::put('distributor_name', $distributor->name);
        Session::put('distributor_email', $distributor->email);

        return redirect()->route('distributor.orders.pending')->with('flash_message_success', '발주사 로그인 성공!');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['distributor_id', 'distributor_name', 'distributor_email']);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('distributor.login')->with('flash_message_success', '로그아웃 되었습니다.');
    }

    public function ordersPending(Request $request)
    {
        return $this->listing($request, false);
    }

    public function ordersCompleted(Request $request)
    {
        return $this->listing($request, true);
    }

    private function listing(Request $request, bool $completed)
    {
        $distributor = $this->currentDistributor();
        if (! $distributor) {
            return redirect()->route('distributor.login');
        }
        $filters = $request->validate([
            'date_type' => 'nullable|in:created_at,shipped_at',
            'from' => 'nullable|date_format:Y-m-d',
            'to' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('from'), 'after_or_equal:from')],
            'search_type' => 'nullable|in:all,order_id,product_name,product_code,channel_name,receiver,tracking_number',
            'keyword' => 'nullable|string|max:100',
            'per_page' => 'nullable|in:10,20,50,100',
            'export' => 'nullable|in:1',
        ]);
        $query = $this->orderItemQuery($distributor);
        $statuses = $completed ? [OrderItemStatus::SHIPPING, OrderItemStatus::DELIVERED, OrderItemStatus::CONFIRMED] : [OrderItemStatus::PAID, OrderItemStatus::READY_TO_SHIP];
        $legacy = $completed ? ['Shipped', 'Delivered', 'Confirmed', '배송중', '배송완료', '구매확정'] : ['Payment Captured', 'New', 'In Process', '결제완료', '배송대기', '배송준비중'];
        // Canonical status takes precedence over an out-of-date legacy label or old tracking number.
        $query->where(fn ($q) => $q->whereIn('status_code', $statuses)->orWhere(fn ($q) => $q->where(fn ($q) => $q->whereNull('status_code')->orWhere('status_code', ''))->whereIn('item_status', $legacy)));
        $date = ($filters['date_type'] ?? 'created_at') === 'shipped_at' ? 'shipped_at' : 'created_at';
        if (! empty($filters['from'])) {
            $query->where($date, '>=', $filters['from'].' 00:00:00');
        }
        if (! empty($filters['to'])) {
            $query->where($date, '<', Carbon::parse($filters['to'])->addDay()->toDateString());
        }
        $keyword = trim($filters['keyword'] ?? '');
        if ($keyword !== '') {
            $type = $filters['search_type'] ?? 'all';
            $query->where(function ($q) use ($type, $keyword) {
                foreach (['product_name', 'product_code', 'tracking_number'] as $field) {
                    if ($type === 'all' || $type === $field) {
                        $q->orWhere($field, 'like', '%'.$keyword.'%');
                    }
                }
                if ($type === 'all' || $type === 'order_id') {
                    $number = preg_replace('/^Me9-Shop-0*/i', '', $keyword);
                    if (ctype_digit($number)) {
                        $q->orWhere('order_id', (int) $number);
                    } elseif ($type === 'order_id') {
                        $q->whereRaw('1 = 0');
                    }
                }
                if ($type === 'all' || $type === 'channel_name') {
                    $q->orWhereHas('shopChannel', fn ($s) => $s->where('channel_name', 'like', '%'.$keyword.'%'));
                }
                if ($type === 'all' || $type === 'receiver') {
                    $q->orWhere('recipient_name', 'like', '%'.$keyword.'%')->orWhere(fn ($q) => $q->whereNull('recipient_name')->whereHas('order', fn ($o) => $o->where('name', 'like', '%'.$keyword.'%')));
                }
            });
        }
        $query->orderByDesc($date)->orderByDesc('id');
        if ($request->boolean('export')) {
            return $this->export($query, $completed);
        }
        $orders = $query->paginate((int) ($filters['per_page'] ?? 20))->withQueryString();
        $orders->through(fn (OrdersProduct $item) => $this->orderItemRow($item));

        return response()->view('distributor.orders', compact('orders', 'filters', 'completed', 'distributor'))->header('Cache-Control', 'private, no-store');
    }

    public function orderDetails($id)
    {
        $distributor = $this->currentDistributor();
        if (! $distributor) {
            return redirect()->route('distributor.login');
        }
        $order = $this->orderItemRow($this->orderItemQuery($distributor)->findOrFail($id));
        $embedded = request()->boolean('frame');

        return response()->view('distributor.order_details', compact('order', 'distributor', 'embedded'))->header('Cache-Control', 'private, no-store');
    }

    public function updateOrder(Request $request, $id)
    {
        $distributor = $this->currentDistributor();
        if (! $distributor) {
            return redirect()->route('distributor.login');
        }
        $data = $request->validate([
            'receiver' => 'sometimes|required|string|max:100',
            'zipcode' => 'sometimes|required|regex:/^[0-9]{5}$/',
            'address' => 'sometimes|required|string|max:500',
            'address_detail' => 'sometimes|nullable|string|max:150',
            'courier' => 'nullable|string|max:100',
            'tracking_no' => 'nullable|string|max:100',
            'status_code' => 'nullable|in:ready_to_ship,shipping,delivered',
        ]);
        DB::transaction(function () use ($distributor, $id, $data) {
            $item = $this->orderItemQuery($distributor)->lockForUpdate()->findOrFail($id);
            if (! in_array($item->normalized_status, [OrderItemStatus::PAID, OrderItemStatus::READY_TO_SHIP, OrderItemStatus::SHIPPING], true)) {
                throw ValidationException::withMessages(['status_code' => '현재 주문의 배송정보가 확정되었습니다. 처리 상태를 확인해 주세요.']);
            }
            $before = $item->only(['status_code', 'courier_name', 'tracking_number', 'recipient_name', 'recipient_postcode', 'recipient_address', 'recipient_address_detail']);
            foreach (['receiver' => 'recipient_name', 'zipcode' => 'recipient_postcode', 'address' => 'recipient_address', 'address_detail' => 'recipient_address_detail', 'courier' => 'courier_name', 'tracking_no' => 'tracking_number'] as $key => $field) {
                if (array_key_exists($key, $data)) {
                    $item->$field = $data[$key];
                }
            }
            $status = $data['status_code'] ?? OrderItemStatus::SHIPPING;
            if (in_array($status, [OrderItemStatus::SHIPPING, OrderItemStatus::DELIVERED], true) && (! filled($item->courier_name) || ! filled($item->tracking_number))) {
                throw ValidationException::withMessages(['tracking_no' => '택배사와 송장번호를 모두 입력해 주세요.']);
            }
            app(OrderFulfillmentService::class)->change($item, $status);
            if ($before !== $item->only(array_keys($before))) {
                $this->logChange($item, $distributor);
            }
        });

        return redirect()->route('distributor.order.details', ['id' => $id, 'frame' => $request->boolean('frame') ? 1 : null])->with('flash_message_success', '발주 배송 정보가 저장되었습니다.');
    }

    public function uploadInvoice(Request $request)
    {
        $distributor = $this->currentDistributor();
        if (! $distributor) {
            return redirect()->route('distributor.login');
        }
        $request->validate(['invoice_file' => 'required|file|max:5120']);
        $rows = app(DistributorInvoiceFile::class)->read($request->file('invoice_file'));
        $updated = DB::transaction(function () use ($distributor, $rows) {
            $items = $this->orderItemQuery($distributor)->whereIn('id', array_column($rows, 'id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($rows as $row) {
                $item = $items->get($row['id']);
                if (! $item || ! in_array($item->normalized_status, [OrderItemStatus::PAID, OrderItemStatus::READY_TO_SHIP], true)) {
                    throw ValidationException::withMessages(['invoice_file' => $row['line'].'행: 본인에게 배정된 발주 대기 상품만 처리할 수 있습니다. 파일의 어느 행도 적용하지 않았습니다.']);
                }
            }
            foreach ($rows as $row) {
                $item = $items[$row['id']];
                $item->courier_name = $row['courier'];
                $item->tracking_number = $row['tracking'];
                app(OrderFulfillmentService::class)->change($item, OrderItemStatus::SHIPPING);
                $this->logChange($item, $distributor);
            }

            return count($rows);
        });

        return redirect()->route('distributor.orders.completed')->with('flash_message_success', "{$updated}건의 송장이 등록되어 발주완료 목록으로 이동했습니다.");
    }

    private function orderItemQuery(Distributor $distributor)
    {
        return OrdersProduct::with(['order', 'shopChannel', 'product'])->where('distributor_id', $distributor->id);
    }

    private function logChange(OrdersProduct $item, Distributor $distributor): void
    {
        $log = new OrdersLog;
        $log->order_id = $item->order_id;
        $log->order_item_id = $item->id;
        $log->order_status = $item->status_label;
        $log->actor = mb_substr('발주사: '.$distributor->name, 0, 100);
        $log->save();
    }

    private function export($query, bool $completed)
    {
        if ((clone $query)->count() > DistributorInvoiceFile::MAX_ROWS) {
            throw ValidationException::withMessages(['export' => '검색 기간을 좁혀 2,000건 이하로 다운로드해 주세요.']);
        }
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet();
        $sheet->setTitle($completed ? '발주 완료' : '발주 대기');
        $headers = ['주문상품ID', '주문번호', 'Shop 채널명', '배송요청일시', '상품코드', '상품명', '구매옵션', '구매수량', '수령인 성명', '우편번호', '배송지 주소', '상세주소', '주문상태', '택배사', '송장번호'];
        $sheet->fromArray($headers, null, 'A1');
        $line = 2;
        foreach ($query->get() as $item) {
            $r = $this->orderItemRow($item);
            $values = [$r['id'], $r['order_id'], $r['channel_name'], $r['request_date'], $r['product_code'], $r['product_name'], $r['option'], $r['quantity'], $r['receiver'], $r['zipcode'], $r['address'], $r['address_detail'], $r['status'], $item->courier_name ?? '', $item->tracking_number ?? ''];
            foreach ($values as $i => $value) {
                $sheet->setCellValueExplicit([$i + 1, $line], $i === 7 ? (int) $value : (string) $value, $i === 7 ? DataType::TYPE_NUMERIC : DataType::TYPE_STRING);
            }
            $line++;
        }
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:O'.max(1, $line - 1));
        $sheet->getStyle('A1:O1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:O1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF334DA8');
        foreach (range('A', 'O') as $c) {
            $sheet->getColumnDimension($c)->setWidth(in_array($c, ['F', 'K', 'L'], true) ? 32 : 20);
        }
        $sheet->getColumnDimension('N')->setWidth(22);
        $sheet->getColumnDimension('O')->setWidth(25);

        return response()->streamDownload(function () use ($book) {
            (new Xlsx($book))->save('php://output');
            $book->disconnectWorksheets();
        }, 'distributor-'.($completed ? 'completed' : 'pending').'-'.now()->format('Ymd-His').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function orderItemRow(OrdersProduct $item): array
    {
        $order = $item->order;
        $override = $item->recipient_address !== null;
        $receiver = $item->recipient_name ?? $order?->name ?? '';
        $address = $item->recipient_address ?? $order?->address ?? '';
        $zipcode = $item->recipient_postcode ?? ($override ? '' : ($order?->pincode ?? ''));
        $detail = $item->recipient_address_detail ?? ($override ? '' : ($order?->address_detail ?? ''));

        return [
            'id' => $item->id, 'order_id' => 'Me9-Shop-'.str_pad((string) $item->order_id, 7, '0', STR_PAD_LEFT),
            'channel_name' => $item->shopChannel?->channel_name ?? 'Me9 Shop',
            'product_code' => $item->product_code, 'product_name' => $item->product_name,
            'option' => collect([$item->product_color, $item->product_size])->filter(fn ($v) => filled($v) && $v !== '-')->implode(' / ') ?: '기본옵션',
            'quantity' => $item->product_qty, 'status' => $item->status_label, 'status_code' => $item->normalized_status,
            'receiver' => $receiver, 'zipcode' => $zipcode, 'address' => $address, 'address_detail' => $detail,
            'full_address' => trim($zipcode.' '.$address.' '.$detail),
            'request_date' => $item->created_at?->format('Y-m-d H:i:s') ?? '-',
            'shipped_date' => $item->shipped_at?->format('Y-m-d H:i:s') ?? '-',
            'courier' => $item->courier_name ?? '', 'tracking_no' => $item->tracking_number ?? '',
            'can_edit_shipping' => in_array($item->normalized_status, [OrderItemStatus::PAID, OrderItemStatus::READY_TO_SHIP, OrderItemStatus::SHIPPING], true),
            'pending' => in_array($item->normalized_status, [OrderItemStatus::PAID, OrderItemStatus::READY_TO_SHIP], true),
            'status_options' => $item->normalized_status === OrderItemStatus::SHIPPING ? ['shipping' => '배송중', 'delivered' => '배송완료'] : ['ready_to_ship' => '배송대기', 'shipping' => '배송중'],
        ];
    }
}
