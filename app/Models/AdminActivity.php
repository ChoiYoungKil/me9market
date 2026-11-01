<?php

namespace App\Models;

use App\Support\OrderClaimActions;
use Illuminate\Database\Eloquent\Model;

class AdminActivity extends Model
{
    protected $fillable = ['admin_id', 'actor', 'action', 'method', 'path', 'metadata'];

    protected $casts = ['metadata' => 'array'];

    public function getDescriptionAttribute(): string
    {
        $operation = $this->metadata['operation'] ?? null;
        if ($this->action === 'admin.order.workspace.process') {
            if ($operation && isset(OrderClaimActions::definitions()[$operation])) {
                return OrderClaimActions::definitions()[$operation]['label'];
            }
            foreach (['cancel' => '취소 요청 접수', 'return' => '반품 요청 접수', 'exchange' => '교환 요청 접수'] as $suffix => $label) {
                if (str_ends_with($this->path, '/'.$suffix)) {
                    return $label;
                }
            }

            return '취소·반품·교환 처리';
        }

        return match ($this->action) {
            'admin.channel_points.approve'=>'포인트 요청 승인',
            'admin.channel_points.reject'=>'포인트 요청 반려',
            'admin.order_managers.store'=>'발주담당자 등록',
            'admin.order_managers.update'=>'발주담당자 수정',
            'admin.order_managers.destroy'=>'발주담당자 삭제',
            'admin.order_managers.portal'=>'발주담당자 화면 연결',
            'admin.products.policies'=>'상품 배송·환불 정책 연결',
            'admin.shipping.store'=>'배송비 정책 등록',
            'admin.shipping.edit'=>'배송비 정책 수정',
            'admin.refund_policies.default'=>'기본 취소/환불안내 수정',
            'admin.settlements.generate'=>'정산자료 생성·갱신',
            'admin.settlements.complete'=>'정산자료 완료 처리',
            'admin.settlements.executions.store'=>'정산 집행내역 등록',
            'admin.content.notices.store' => '공지사항 등록',
            'admin.content.notices.update' => '공지사항 수정',
            'admin.content.comments.store' => '관리 댓글 등록',
            'admin.content.comments.update' => '관리 댓글 수정',
            'admin.lists.bulk' => str_contains($this->path, '/products/') ? '상품 상태 일괄 변경' : '회원 상태 일괄 변경',
            default => str_contains($this->path, 'update-order-item-status') ? '주문상품 상태 변경' : (($this->metadata['changes'][0]['resource']??'관리 정보').' '.(['created'=>'등록','updated'=>'수정','deleted'=>'삭제'][$this->metadata['changes'][0]['kind']??'']??'변경')),
        };
    }
}
