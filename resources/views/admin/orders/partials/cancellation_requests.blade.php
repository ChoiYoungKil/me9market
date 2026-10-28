@if($cancellationClaims->isNotEmpty())
<section data-cancellation-requests aria-labelledby="cancellation-requests-title" style="margin-bottom:32px">
    <h3 id="cancellation-requests-title" style="font-size:20px;margin-bottom:15px">취소 신청 내역</h3>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;min-width:600px">
            <thead><tr style="background:#f5f3fa;text-align:left"><th style="padding:12px">접수일</th><th style="padding:12px">주문상품</th><th style="padding:12px">취소 사유</th><th style="padding:12px">처리상태</th></tr></thead>
            <tbody>
                @foreach($cancellationClaims as $claim)
                <tr style="border-bottom:1px solid #e6e0ed">
                    <td style="padding:12px;white-space:nowrap">{{ $claim->created_at->format('Y-m-d H:i') }}</td>
                    <td style="padding:12px">{{ $claim->product?->product_name ?: '주문상품 정보 없음' }}</td>
                    <td style="padding:12px"><strong>{{ $claim->reason }}</strong>@if($claim->detail_reason)<p style="white-space:pre-wrap;overflow-wrap:anywhere;margin-top:6px">{{ $claim->detail_reason }}</p>@endif</td>
                    <td style="padding:12px;white-space:nowrap">{{ ['requested'=>'접수 대기', 'completed'=>'처리 완료', 'rejected'=>'신청 반려'][$claim->status] ?? $claim->status }}<br><small>{{ $claim->product?->status_label }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif
