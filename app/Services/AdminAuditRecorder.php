<?php

namespace App\Services;

use App\Models\Notice;
use App\Models\Product;
use App\Models\User;
use App\Support\OrderItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/** Request-scoped capture of actual model changes, never raw request bodies. */
class AdminAuditRecorder
{
    private bool $active = false;

    private array $changes = [];

    private array $fallback = [];

    private const RESOURCES = [
        'products' => '상품', 'users' => '회원', 'admins' => '관리자', 'vendors' => '회원사',
        'contacts'=>'제휴/문의', 'channel_delivery_charges'=>'배송비 정책', 'shop_cancel_refund_policies'=>'취소/환불안내', 'platform_refund_policies'=>'기본 취소/환불안내', 'channel_point_transactions'=>'판매자 포인트', 'settlement_runs'=>'정산 자료', 'settlement_executions'=>'정산 집행',
        'notices' => '공지사항', 'admin_notice_comments' => '관리 댓글', 'faqs' => '자주묻는질문',
        'orders' => '주문', 'orders_products' => '주문상품', 'order_claims' => '취소·반품·교환 신청',
        'categories' => '분류', 'sections' => '섹션', 'brands' => '브랜드', 'banners' => '배너',
        'coupons' => '쿠폰', 'shop_channels' => 'Shop 채널', 'distributors' => '발주담당자',
    ];

    private const FIELDS = [
        'subject'=>'문의 제목', 'question'=>'질문', 'answer'=>'답변', 'category'=>'카테고리', 'order'=>'정렬순서', 'points'=>'포인트', 'amount'=>'금액', 'base_fee'=>'기본 배송비', 'free_order_amount'=>'무료배송 금액 기준', 'free_order_quantity'=>'무료배송 수량 기준', 'shipping_type'=>'배송비 유형', 'payment_type'=>'배송비 결제', 'access_started_at'=>'접속 시작일', 'access_ended_at'=>'접속 종료일', 'period'=>'정산월', 'executed_at'=>'집행일', 'total_amount'=>'정산금액', 'title' => '제목', 'name' => '이름', 'username' => '아이디', 'product_name' => '상품명',
        'shipping_policy_id'=>'배송비 정책 번호', 'cancel_refund_policy_id'=>'취소/환불 정책 번호', 'shipping_policy_name'=>'배송비 정책명', 'shipping_policy_type'=>'상품 배송비 유형', 'product_code' => '상품코드', 'category_name' => '분류명', 'channel_name' => '채널명',
        'status' => '상태', 'status_code' => '주문 상태', 'item_status' => '주문 상태',
        'type' => '구분', 'vendor_id' => '회원사 번호', 'is_important' => '중요 공지',
        'product_price' => '상품가격', 'selling_price' => '판매가격', 'product_qty' => '수량',
        'stock' => '재고', 'price_delta' => '옵션 추가금', 'size' => '옵션',
        'courier_name' => '택배사', 'tracking_number' => '송장번호', 'settlement_status' => '정산 상태',
        'refund_status' => '환불 상태', 'refund_cash_amount' => '현금 환불금액',
        'pickup_method' => '회수 방법', 'customer_courier_name' => '회수 택배사',
        'customer_tracking_number' => '회수 송장번호', 'return_shipping_fee' => '반품 배송비',
        'exchange_shipping_fee' => '교환 배송비', 'is_public' => '공개 여부',
        'closure_status' => '운영중지 상태', 'attachment' => '첨부파일',
        'content' => '내용', 'description' => '설명', 'admin_reply' => '답변',
    ];

    public function start(Request $request): void
    {
        $this->reset();
        $this->active = true;
        // Some legacy status handlers use query-builder updates without model events.
        foreach (['user' => User::class, 'product' => Product::class, 'notice' => Notice::class] as $key => $class) {
            $id = $request->input($key.'_id');
            if ($request->is('admin/update-'.$key.'-status') && is_scalar($id) && ctype_digit((string) $id)) {
                if ($row = $class::find($id)) {
                    $this->fallback[] = clone $row;
                }
            }
        }
    }

    public function capture(string $event, mixed $model): void
    {
        if (! $this->active || ! $model instanceof Model || ! isset(self::RESOURCES[$model->getTable()]) || count($this->changes) >= 500) {
            return;
        }
        $kind = explode(':', substr($event, strlen('eloquent.')))[0];
        if (! in_array($kind, ['created', 'updated', 'deleted'], true)) {
            return;
        }
        $old = $kind === 'created' ? [] : $model->getRawOriginal();
        $new = $kind === 'deleted' ? [] : $model->getAttributes();
        $this->append($model, $old, $new, $kind);
    }

    private function append(Model $model, array $old, array $new, string $kind): void
    {
        $fields = [];
        $table = $model->getTable();
        foreach (self::FIELDS as $key => $label) {
            if ($key === 'item_status' && (! empty($old['status_code']) || ! empty($new['status_code']))) {
                continue;
            }
            $before = $old[$key] ?? null;
            $after = $new[$key] ?? null;
            if ((string) $before === (string) $after) {
                continue;
            }
            if (in_array($key, ['content', 'description', 'admin_reply', 'answer'], true)) {
                $before = $before === null ? null : '기존 내용';
                $after = $after === null ? null : '내용 '.($kind === 'created' ? '등록' : '수정');
            } else {
                $before = $this->value($table, $key, $before);
                $after = $this->value($table, $key, $after);
                if ($before === $after) continue;
            }
            $fields[] = ['field' => $key, 'label' => $label, 'before' => $before, 'after' => $after];
        }
        if (! $fields && $kind === 'updated') {
            return;
        }
        $data = $new ?: $old;
        $name = $data['title'] ?? $data['question'] ?? $data['subject'] ?? $data['product_name'] ?? $data['channel_name'] ?? $data['category_name'] ?? $data['name'] ?? self::RESOURCES[$table];
        $url = match ($table) {
            'products' => '/admin/add-edit-product/'.$model->getKey(),
            'users' => '/admin/add-edit-user/'.$model->getKey(),
            'faqs'=>'/admin/add-edit-faq/'.$model->getKey(),
            'contacts'=>'/admin/view-contact/'.$model->getKey(),
            'channel_delivery_charges'=>'/admin/edit-shipping-charges/'.$model->getKey(),
            'notices' => '/admin/content/notices/'.$model->getKey(),
            'admin_notice_comments' => '/admin/content/notices/'.($data['notice_id'] ?? ''),
            'orders_products','order_claims' => '/admin/orders/'.($data['order_id'] ?? '').'?tab=history',
            'orders' => '/admin/orders/'.$model->getKey(), default => null,
        };
        $this->changes[] = ['resource' => self::RESOURCES[$table], 'table' => $table, 'id' => $model->getKey(),
            'name' => mb_substr((string) $name, 0, 200), 'kind' => $kind, 'fields' => $fields, 'url' => $kind === 'deleted' ? null : $url];
    }

    private function value(string $table, string $key, mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if ($key === 'status_code') {
            return OrderItemStatus::label((string) $value);
        }
        if (in_array($key, ['is_important', 'is_public'], true)) {
            return (int) $value === 1 ? '예' : '아니오';
        }
        if ($key === 'status' && $table === 'notices') {
            return (int) $value === 1 ? '공개' : '비공개';
        }
        if ($key === 'status' && in_array((string) $value, ['0', '1'], true)) {
            return (int) $value === 1 ? '활성' : '비활성';
        }
        if ($key === 'pickup_method') {
            return ['automatic' => '자동회수', 'manual' => '수동회수'][$value] ?? (string) $value;
        }
        if ($key === 'type') {
            return ['cancel' => '취소', 'return' => '반품', 'exchange' => '교환', 'purchase'=>'구매', 'refund'=>'환급', 'customer_payback'=>'고객 페이백', 'first_visit'=>'첫 방문 포인트', 'sms'=>'문자 차감'][$value] ?? (string) $value;
        }
        if ($key === 'status' && $table === 'channel_point_transactions') return ['pending'=>'승인대기','approved'=>'승인완료','rejected'=>'반려'][$value]??(string)$value;
        if ($key === 'status' && $table === 'order_claims') {
            return ['requested' => '신청', 'approved' => '승인', 'received' => '회수완료', 'held' => '보류', 'held_before' => '회수 전 보류', 'held_after' => '회수 후 보류', 'completed' => '완료', 'rejected' => '거절', 'withdrawn' => '철회'][$value] ?? (string) $value;
        }

        return mb_substr(is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE), 0, 500);
    }

    public function finish(): array
    {
        foreach ($this->fallback as $old) {
            $captured = collect($this->changes)->contains(fn ($row) => $row['table'] === $old->getTable() && $row['id'] === $old->getKey());
            if (! $captured && $new = $old->fresh()) {
                $this->append($old, $old->getRawOriginal(), $new->getAttributes(), 'updated');
            }
        }
        $result = $this->changes;
        $this->reset();

        return $result;
    }

    public function reset(): void
    {
        $this->active = false;
        $this->changes = [];
        $this->fallback = [];
    }
}
