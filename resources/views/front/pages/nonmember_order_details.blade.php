@extends('layouts.frontend')

@section('page_type', 'sub')

@section('content')
<style>
    #container {
        padding-top: 100px;
    }
    @media all and (max-width: 1024px) {
        #container {
            padding-top: 70px;
        }
    }
    /* Premium style additions for modals */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        animation: modalFadeIn 0.3s ease;
    }
    .modal-box {
        background: #fff;
        border-radius: 16px;
        width: 520px;
        max-width: 90%;
        padding: 30px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        position: relative;
    }
    .modal-header {
        font-size: 20px;
        font-weight: 700;
        color: #111;
        margin-bottom: 20px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-header span {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .modal-close {
        cursor: pointer;
        background: none;
        border: none;
        font-size: 24px;
        color: #94a3b8;
        padding: 0;
        line-height: 1;
    }
    .modal-body label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 6px;
        color: #333;
        margin-top: 12px;
    }
    .modal-body label:first-child {
        margin-top: 0;
    }
    .modal-body select, .modal-body textarea, .modal-body input[type="text"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 14px;
        box-sizing: border-box;
        margin-top: 4px;
        background: #fff;
    }
    .modal-body textarea {
        resize: none;
        height: 80px;
    }
    .modal-body input[readonly] {
        background: #f1f5f9;
        color: #64748b;
        cursor: not-allowed;
    }
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 25px;
        border-top: 1px solid #e2e8f0;
        padding-top: 15px;
    }
    .modal-btn {
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        border: none;
        transition: background 0.2s;
    }
    .modal-btn.primary {
        background: #6366f1;
        color: white;
    }
    .modal-btn.primary:hover {
        background: #4f46e5;
    }
    .modal-btn.secondary {
        background: #f1f5f9;
        color: #475569;
    }
    .modal-btn.secondary:hover {
        background: #e2e8f0;
    }
    .modal-product-info {
        background: #f8fafc;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #e2e8f0;
    }
    .modal-product-name {
        font-weight: bold;
        color: #1e293b;
        font-size: 14px;
    }
    .modal-product-option {
        font-size: 12px;
        color: #64748b;
        margin-top: 4px;
    }
    .radio-group {
        display: flex;
        gap: 15px;
        margin-top: 6px;
    }
    .radio-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: normal !important;
        font-size: 14px;
        cursor: pointer;
    }
    .radio-label input {
        margin: 0;
        cursor: pointer;
    }
    .star-btn {
        font-size: 32px;
        cursor: pointer;
        color: #cbd5e1;
        transition: color 0.1s;
    }
    .star-btn.active {
        color: #f59e0b;
    }
    @keyframes modalFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>

<div id="container">
    <div id="contents">
        <div class="row" style="padding: 40px 0;">
            <div class="box box1" style="max-width: 1000px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px;">
                
                @if(session('flash_message_success'))
                    <div style="background: #ecfdf5; border: 1px solid #10b981; color: #047857; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-weight: bold;">
                        {{ session('flash_message_success') }}
                    </div>
                @endif
                @if(session('flash_message_error'))
                    <div style="background: #fef2f2; border: 1px solid #ef4444; color: #b91c1c; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-weight: bold;">
                        {{ session('flash_message_error') }}
                    </div>
                @endif

                <div style="border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="font-size: 24px; font-weight: 700; color: #111; margin: 0 0 5px 0;">주문 상세 정보 (비회원)</h2>
                        <p style="font-size: 14px; color: #666; margin: 0;">주문번호 : <strong style="color: #6366f1;">Me9-Shop-{{ sprintf('%07d', $order->id) }}</strong> | 주문일자 : {{ $order->created_at->format('Y-m-d') }}</p>
                    </div>
                    <span style="background: rgba(99, 102, 241, 0.1); color: #6366f1; padding: 6px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;">
                        {{ $order->order_status }}
                    </span>
                </div>

                @php
                    $purchasedItems = $order->orders_products->filter(function($p) {
                        return !in_array($p->item_status, ['Confirmed', 'Cancelled', 'Cancel Requested', 'Return Requested', 'Returned', 'Exchange Requested', 'Exchanged']);
                    });
                    $confirmedItems = $order->orders_products->filter(function($p) {
                        return $p->item_status == 'Confirmed';
                    });
                    $cancelledItems = $order->orders_products->filter(function($p) {
                        return in_array($p->item_status, ['Cancel Requested', 'Cancelled']);
                    });
                    $returnedItems = $order->orders_products->filter(function($p) {
                        return in_array($p->item_status, ['Return Requested', 'Returned']);
                    });
                    $exchangedItems = $order->orders_products->filter(function($p) {
                        return in_array($p->item_status, ['Exchange Requested', 'Exchanged']);
                    });
                @endphp

                <!-- 1. 구매 상품 목록 -->
                <div style="margin-bottom: 45px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #222; margin-bottom: 15px; border-left: 4px solid #6366f1; padding-left: 10px;">구매 상품</h3>
                    <table style="width: 100%; border-collapse: collapse; border-top: 2px solid #333;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 15px; font-size: 13px; font-weight: 600; text-align: left;">상품정보</th>
                                <th style="padding: 15px; font-size: 13px; font-weight: 600; text-align: center; width: 100px;">수량</th>
                                <th style="padding: 15px; font-size: 13px; font-weight: 600; text-align: right; width: 150px;">가격</th>
                                <th style="padding: 15px; font-size: 13px; font-weight: 600; text-align: center; width: 300px;">신청/처리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchasedItems as $prod)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 20px 15px; text-align: left;">
                                    <strong style="display: block; font-size: 15px; color: #111;">{{ $prod->product_name }}</strong>
                                    <span style="font-size: 13px; color: #666; margin-top: 4px; display: block;">코드: {{ $prod->product_code }} | 옵션: {{ $prod->product_size }}</span>
                                </td>
                                <td style="padding: 20px 15px; text-align: center; font-size: 14px; color: #333;">{{ $prod->product_qty }}개</td>
                                <td style="padding: 20px 15px; text-align: right; font-size: 14px; color: #333; font-weight: 600;">{{ number_format($prod->product_price) }} 원</td>
                                <td style="padding: 20px 15px; text-align: center;">
                                    <div style="display: flex; flex-wrap: wrap; gap: 6px; justify-content: center;">
                                        @if(in_array($prod->item_status, ['New', 'In Process']))
                                            <button onclick="openCancelModal({{ $prod->id }}, '{{ addslashes($prod->product_name) }}', '옵션: {{ $prod->product_size }} / 수량: {{ $prod->product_qty }}개')" style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; font-weight: 600;">취소신청</button>
                                        @endif
                                        @if(in_array($prod->item_status, ['Shipped', 'Delivered']))
                                            <button onclick="openReturnModal({{ $prod->id }}, '{{ addslashes($prod->product_name) }}', '옵션: {{ $prod->product_size }} / 수량: {{ $prod->product_qty }}개')" style="background: #f59e0b; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; font-weight: 600;">반품신청</button>
                                            <button onclick="openExchangeModal({{ $prod->id }}, '{{ addslashes($prod->product_name) }}', '옵션: {{ $prod->product_size }} / 수량: {{ $prod->product_qty }}개')" style="background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; font-weight: 600;">교환신청</button>
                                        @endif
                                        <button onclick="openQnaModal({{ $prod->id }}, '{{ addslashes($prod->product_name) }}', '옵션: {{ $prod->product_size }}')" style="background: #475569; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; font-weight: 600;">상품문의</button>
                                        <button onclick="openConfirmPurchaseModal({{ $prod->id }}, '{{ addslashes($prod->product_name) }}', '옵션: {{ $prod->product_size }}')" style="background: #10b981; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; font-weight: 600;">구매확정</button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="padding: 30px; text-align: center; color: #888; font-size: 14px;">주문 완료된 구매 상품이 없습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 2. 구매확정 완료 상품 목록 -->
                <div style="margin-bottom: 45px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #222; margin-bottom: 15px; border-left: 4px solid #10b981; padding-left: 10px;">구매확정 완료 상품</h3>
                    <table style="width: 100%; border-collapse: collapse; border-top: 2px solid #10b981;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: left; width: 180px;">확정일자</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: left;">상품정보</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: center; width: 100px;">수량</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: right; width: 150px;">가격</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: center; width: 120px;">처리상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($confirmedItems as $prod)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; text-align: left; font-size: 13px; color: #666;">
                                    {{ $prod->updated_at->format('Y-m-d H:i') }}
                                </td>
                                <td style="padding: 15px; text-align: left;">
                                    <strong style="display: block; font-size: 14px; color: #111;">{{ $prod->product_name }}</strong>
                                    <span style="font-size: 12px; color: #666; margin-top: 2px; display: block;">옵션: {{ $prod->product_size }}</span>
                                </td>
                                <td style="padding: 15px; text-align: center; font-size: 13px; color: #333;">{{ $prod->product_qty }}개</td>
                                <td style="padding: 15px; text-align: right; font-size: 13px; color: #333; font-weight: 600;">{{ number_format($prod->product_price) }} 원</td>
                                <td style="padding: 15px; text-align: center;">
                                    <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">구매확정</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="padding: 25px; text-align: center; color: #888; font-size: 13px;">구매확정 완료된 내역이 없습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 3. 취소 신청/완료 상품 목록 -->
                <div style="margin-bottom: 45px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #222; margin-bottom: 15px; border-left: 4px solid #ef4444; padding-left: 10px;">취소 신청/완료 상품</h3>
                    <table style="width: 100%; border-collapse: collapse; border-top: 2px solid #ef4444;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: left; width: 180px;">접수일자</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: left;">상품정보</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: center; width: 80px;">수량</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: left;">취소사유</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: center; width: 120px;">처리상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cancelledItems as $prod)
                                @php
                                    $claim = $order->claims->firstWhere('order_product_id', $prod->id);
                                @endphp
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; text-align: left; font-size: 13px; color: #666;">
                                    {{ $claim ? $claim->created_at->format('Y-m-d H:i') : $prod->updated_at->format('Y-m-d H:i') }}
                                </td>
                                <td style="padding: 15px; text-align: left;">
                                    <strong style="display: block; font-size: 14px; color: #111;">{{ $prod->product_name }}</strong>
                                    <span style="font-size: 12px; color: #666; margin-top: 2px; display: block;">옵션: {{ $prod->product_size }}</span>
                                </td>
                                <td style="padding: 15px; text-align: center; font-size: 13px; color: #333;">{{ $prod->product_qty }}개</td>
                                <td style="padding: 15px; text-align: left; font-size: 13px; color: #555;">
                                    @if($claim)
                                        <strong>{{ $claim->reason }}</strong>
                                        @if($claim->detail_reason)
                                            <span style="display: block; font-size: 12px; color: #888; margin-top: 2px;">상세: {{ $claim->detail_reason }}</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <span style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                                        {{ $prod->item_status == 'Cancel Requested' ? '취소신청' : '취소완료' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="padding: 25px; text-align: center; color: #888; font-size: 13px;">취소 완료되거나 신청된 내역이 없습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 4. 반품 신청/완료 상품 목록 -->
                <div style="margin-bottom: 45px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #222; margin-bottom: 15px; border-left: 4px solid #f59e0b; padding-left: 10px;">반품 신청/완료 상품</h3>
                    <table style="width: 100%; border-collapse: collapse; border-top: 2px solid #f59e0b;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: left; width: 180px;">접수일자</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: left;">상품정보</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: center; width: 80px;">수량</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: left;">반품정보 및 사유</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: center; width: 120px;">처리상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returnedItems as $prod)
                                @php
                                    $claim = $order->claims->firstWhere('order_product_id', $prod->id);
                                @endphp
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; text-align: left; font-size: 13px; color: #666;">
                                    {{ $claim ? $claim->created_at->format('Y-m-d H:i') : $prod->updated_at->format('Y-m-d H:i') }}
                                </td>
                                <td style="padding: 15px; text-align: left;">
                                    <strong style="display: block; font-size: 14px; color: #111;">{{ $prod->product_name }}</strong>
                                    <span style="font-size: 12px; color: #666; margin-top: 2px; display: block;">옵션: {{ $prod->product_size }}</span>
                                </td>
                                <td style="padding: 15px; text-align: center; font-size: 13px; color: #333;">{{ $prod->product_qty }}개</td>
                                <td style="padding: 15px; text-align: left; font-size: 13px; color: #555;">
                                    @if($claim)
                                        <strong>사유: {{ $claim->reason }}</strong>
                                        @if($claim->detail_reason)
                                            <span style="display: block; font-size: 12px; color: #888; margin-top: 2px;">{{ $claim->detail_reason }}</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <span style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                                        {{ $prod->item_status == 'Return Requested' ? '반품신청' : '반품완료' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="padding: 25px; text-align: center; color: #888; font-size: 13px;">반품 신청된 내역이 없습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 5. 교환 신청/완료 상품 목록 -->
                <div style="margin-bottom: 45px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #222; margin-bottom: 15px; border-left: 4px solid #3b82f6; padding-left: 10px;">교환 신청/완료 상품</h3>
                    <table style="width: 100%; border-collapse: collapse; border-top: 2px solid #3b82f6;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: left; width: 180px;">접수일자</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: left;">상품정보</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: center; width: 80px;">수량</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: left;">교환정보 및 사유</th>
                                <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; text-align: center; width: 120px;">처리상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exchangedItems as $prod)
                                @php
                                    $claim = $order->claims->firstWhere('order_product_id', $prod->id);
                                @endphp
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; text-align: left; font-size: 13px; color: #666;">
                                    {{ $claim ? $claim->created_at->format('Y-m-d H:i') : $prod->updated_at->format('Y-m-d H:i') }}
                                </td>
                                <td style="padding: 15px; text-align: left;">
                                    <strong style="display: block; font-size: 14px; color: #111;">{{ $prod->product_name }}</strong>
                                    <span style="font-size: 12px; color: #666; margin-top: 2px; display: block;">옵션: {{ $prod->product_size }}</span>
                                </td>
                                <td style="padding: 15px; text-align: center; font-size: 13px; color: #333;">{{ $prod->product_qty }}개</td>
                                <td style="padding: 15px; text-align: left; font-size: 13px; color: #555;">
                                    @if($claim)
                                        <strong>사유: {{ $claim->reason }}</strong>
                                        @if($claim->detail_reason)
                                            <span style="display: block; font-size: 12px; color: #888; margin-top: 2px;">{{ $claim->detail_reason }}</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <span style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                                        {{ $prod->item_status == 'Exchange Requested' ? '교환신청' : '교환완료' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="padding: 25px; text-align: center; color: #888; font-size: 13px;">교환 신청된 내역이 없습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 배송 정보 및 결제 정보 -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 20px;">
                    <div>
                        <h3 style="font-size: 18px; font-weight: 700; color: #222; margin-bottom: 15px; border-left: 4px solid #6366f1; padding-left: 10px;">배송지 정보</h3>
                        <table style="width: 100%;">
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 10px 0; font-size: 14px; font-weight: 600; color: #666; width: 100px;">수령인</td>
                                <td style="padding: 10px 0; font-size: 14px; color: #333;">{{ $order->name }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 10px 0; font-size: 14px; font-weight: 600; color: #666;">연락처</td>
                                <td style="padding: 10px 0; font-size: 14px; color: #333;">{{ $order->mobile }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 10px 0; font-size: 14px; font-weight: 600; color: #666; vertical-align: top;">배송주소</td>
                                <td style="padding: 10px 0; font-size: 14px; color: #333; line-height: 1.5;">[{{ $order->pincode }}]<br>{{ $order->address }}</td>
                            </tr>
                        </table>
                    </div>
                    <div>
                        <h3 style="font-size: 18px; font-weight: 700; color: #222; margin-bottom: 15px; border-left: 4px solid #6366f1; padding-left: 10px;">결제 정보</h3>
                        <table style="width: 100%;">
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 10px 0; font-size: 14px; font-weight: 600; color: #666; width: 100px;">상품합계</td>
                                <td style="padding: 10px 0; font-size: 14px; color: #333; text-align: right;">{{ number_format($order->grand_total - $order->shipping_charges) }} 원</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 10px 0; font-size: 14px; font-weight: 600; color: #666;">배송비</td>
                                <td style="padding: 10px 0; font-size: 14px; color: #333; text-align: right;">{{ number_format($order->shipping_charges) }} 원</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 10px 0; font-size: 16px; font-weight: 700; color: #111;">최종 결제금액</td>
                                <td style="padding: 10px 0; font-size: 18px; color: #6366f1; font-weight: 700; text-align: right;">{{ number_format($order->grand_total) }} 원</td>
                            </tr>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- 1. 취소 신청 모달 (Slide 19 / RF-01-06-02-1) -->
<div id="cancel-modal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-header">
            <span>○ 취소신청하기</span>
            <button class="modal-close" onclick="closeModal('cancel-modal')">&times;</button>
        </div>
        <form action="{{ route('front.nonmember.order_claim.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <input type="hidden" name="order_product_id" value="">
            <input type="hidden" name="type" value="cancel">
            
            <div class="modal-body">
                <label>주문상품</label>
                <div class="modal-product-info">
                    <div class="modal-product-name">상품명 로딩중...</div>
                    <div class="modal-product-option">옵션 정보 로딩중...</div>
                </div>

                <label>취소사유</label>
                <select name="reason" id="cancel_reason" onchange="toggleCancelEtc()">
                    <option value="고객 단순 변심">고객 단순 변심</option>
                    <option value="배송 지연">배송 지연</option>
                    <option value="기타">기타</option>
                </select>
                <div id="cancel_etc_wrap" style="display: none;">
                    <label>기타내용</label>
                    <textarea name="detail_reason" placeholder="기타 내용을 입력해 주세요."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="modal-btn primary">취소신청</button>
                <button type="button" class="modal-btn secondary" onclick="closeModal('cancel-modal')">창닫기</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. 반품 신청 모달 (Slide 20 / RF-01-06-02-2) -->
<div id="return-modal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-header">
            <span>○ 반품신청하기</span>
            <button class="modal-close" onclick="closeModal('return-modal')">&times;</button>
        </div>
        <form action="{{ route('front.nonmember.order_claim.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <input type="hidden" name="order_product_id" value="">
            <input type="hidden" name="type" value="return">

            <div class="modal-body">
                <label>주문상품</label>
                <div class="modal-product-info">
                    <div class="modal-product-name">상품명 로딩중...</div>
                    <div class="modal-product-option">옵션 정보 로딩중...</div>
                </div>

                <label>반품사유</label>
                <select name="reason">
                    <option value="단순 변심">단순 변심</option>
                    <option value="상품 불량">상품 불량</option>
                    <option value="오배송">오배송</option>
                    <option value="기타">기타</option>
                </select>

                <label>상품회수방법</label>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="recovery_method" value="자동회수" checked onchange="toggleReturnAddress(true)">
                        자동회수
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="recovery_method" value="수동회수" onchange="toggleReturnAddress(false)">
                        수동회수
                    </label>
                </div>

                <div id="return-auto-info" style="margin-top: 10px; font-size: 13px; color: #6366f1;">
                    * 자동 회수 처리가 진행됩니다. 반품 택배 기사님이 2-3일 내에 방문 예정입니다.
                </div>

                <div id="return-address-wrap" style="display: none; margin-top: 10px;">
                    <label>상품회수주소</label>
                    <input type="text" name="recovery_address" value="20202 서울시 마포구 공덕동 2003" readonly>
                    <p style="font-size: 12px; color: #f59e0b; margin: 4px 0 0 0;">* 수동 회수 시, 위 주소로 상품을 직접 발송해 주셔야 합니다.</p>
                </div>

                <label>기타내용</label>
                <textarea name="detail_reason" placeholder="기타 내용을 입력해 주세요"></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" class="modal-btn primary">반품신청</button>
                <button type="button" class="modal-btn secondary" onclick="closeModal('return-modal')">창닫기</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. 교환 신청 모달 (Slide 21 / RF-01-06-02-3) -->
<div id="exchange-modal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-header">
            <span>○ 교환신청하기</span>
            <button class="modal-close" onclick="closeModal('exchange-modal')">&times;</button>
        </div>
        <form action="{{ route('front.nonmember.order_claim.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <input type="hidden" name="order_product_id" value="">
            <input type="hidden" name="type" value="exchange">

            <div class="modal-body">
                <label>주문상품</label>
                <div class="modal-product-info">
                    <div class="modal-product-name">상품명 로딩중...</div>
                    <div class="modal-product-option">옵션 정보 로딩중...</div>
                </div>

                <label>교환사유</label>
                <select name="reason">
                    <option value="사이즈 변경">사이즈 변경</option>
                    <option value="색상 변경">색상 변경</option>
                    <option value="제품 불량">제품 불량</option>
                    <option value="기타">기타</option>
                </select>

                <label>상품회수방법</label>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="recovery_method" value="자동회수" checked onchange="toggleExchangeAddress(true)">
                        자동회수
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="recovery_method" value="수동회수" onchange="toggleExchangeAddress(false)">
                        수동회수
                    </label>
                </div>

                <div id="exchange-auto-info" style="margin-top: 10px; font-size: 13px; color: #6366f1;">
                    * 자동 교환 회수 처리가 진행됩니다. 교환 배송비가 발생할 수 있습니다.
                </div>

                <div id="exchange-address-wrap" style="display: none; margin-top: 10px;">
                    <label>상품회수주소</label>
                    <input type="text" name="recovery_address" value="20202 서울시 마포구 공덕동 2003" readonly>
                    <p style="font-size: 12px; color: #f59e0b; margin: 4px 0 0 0;">* 수동 회수 시, 위 주소로 교환 상품을 직접 발송해 주셔야 합니다.</p>
                </div>

                <label>기타내용 (교환할 옵션 상세)</label>
                <textarea name="detail_reason" placeholder="기타 내용 및 교환할 옵션(색상/사이즈 등)을 입력해 주세요"></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" class="modal-btn primary">교환신청</button>
                <button type="button" class="modal-btn secondary" onclick="closeModal('exchange-modal')">창닫기</button>
            </div>
        </form>
    </div>
</div>

<!-- 4. 구매확정 모달 (Slide 22 / RF-01-06-02-4) -->
<div id="confirm-modal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-header">
            <span>○ 구매확정하기</span>
            <button class="modal-close" onclick="closeModal('confirm-modal')">&times;</button>
        </div>
        <form action="{{ route('front.nonmember.order_claim.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <input type="hidden" name="order_product_id" value="">
            <input type="hidden" name="type" value="confirm">
            <input type="hidden" name="reason" value="구매 확정">

            <div class="modal-body" style="text-align: center;">
                <label style="text-align: left;">주문상품</label>
                <div class="modal-product-info" style="text-align: left;">
                    <div class="modal-product-name">상품명 로딩중...</div>
                    <div class="modal-product-option">옵션 정보 로딩중...</div>
                </div>

                <label style="text-align: center; margin-top: 20px;">별점주기</label>
                <div style="font-size: 28px; display: flex; gap: 8px; justify-content: center; margin: 10px 0;">
                    <span class="star-btn" data-value="1" onclick="setRating(1)">★</span>
                    <span class="star-btn" data-value="2" onclick="setRating(2)">★</span>
                    <span class="star-btn" data-value="3" onclick="setRating(3)">★</span>
                    <span class="star-btn" data-value="4" onclick="setRating(4)">★</span>
                    <span class="star-btn" data-value="5" onclick="setRating(5)">★</span>
                </div>
                <input type="hidden" name="rating" id="rating-value" value="5">

                <label style="text-align: left; margin-top: 15px;">이 상품을 구매하겠습니다.</label>
                <textarea name="review" placeholder="상품에 대한 평가 및 만족도를 작성해주세요." style="text-align: left;">이 상품을 구매하겠습니다.</textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" class="modal-btn primary">구매확정하기</button>
                <button type="button" class="modal-btn secondary" onclick="closeModal('confirm-modal')">창닫기</button>
            </div>
        </form>
    </div>
</div>

<!-- 5. 상품 문의 모달 (Slide 23 / RF-01-06-02-5) -->
<div id="qna-modal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-header">
            <span>○ 상품 문의하기</span>
            <button class="modal-close" onclick="closeModal('qna-modal')">&times;</button>
        </div>
        <form action="{{ route('front.nonmember.order_inquiry.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <input type="hidden" name="order_product_id" value="">

            <div class="modal-body">
                <div style="font-size: 13px; color: #888; margin-bottom: 5px;">의류 &gt; 티셔츠</div>
                <div class="modal-product-info">
                    <div class="modal-product-name">상품명 로딩중...</div>
                    <div class="modal-product-option">옵션 정보 로딩중...</div>
                </div>

                <label>질문 제목</label>
                <input type="text" name="subject" placeholder="질문 제목입니다" required>

                <label>■ 문의내용</label>
                <textarea name="message" placeholder="판매자에게 상품, 배송, 취소, 교환, 반품 등 궁금한 내용을 문의하세요." required style="height: 120px;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" class="modal-btn primary">문의하기</button>
                <button type="button" class="modal-btn secondary" onclick="closeModal('qna-modal')">창닫기</button>
            </div>
        </form>
    </div>
</div>

<script>
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    function openCancelModal(productId, name, option) {
        let modal = document.getElementById('cancel-modal');
        modal.querySelector('input[name="order_product_id"]').value = productId;
        modal.querySelector('.modal-product-name').innerText = name;
        modal.querySelector('.modal-product-option').innerText = option;
        modal.style.display = 'flex';
        toggleCancelEtc();
    }

    function toggleCancelEtc() {
        let reason = document.getElementById('cancel_reason').value;
        document.getElementById('cancel_etc_wrap').style.display = (reason === '기타') ? 'block' : 'none';
    }

    function openReturnModal(productId, name, option) {
        let modal = document.getElementById('return-modal');
        modal.querySelector('input[name="order_product_id"]').value = productId;
        modal.querySelector('.modal-product-name').innerText = name;
        modal.querySelector('.modal-product-option').innerText = option;
        modal.style.display = 'flex';
    }

    function toggleReturnAddress(isAuto) {
        document.getElementById('return-auto-info').style.display = isAuto ? 'block' : 'none';
        document.getElementById('return-address-wrap').style.display = isAuto ? 'none' : 'block';
    }

    function openExchangeModal(productId, name, option) {
        let modal = document.getElementById('exchange-modal');
        modal.querySelector('input[name="order_product_id"]').value = productId;
        modal.querySelector('.modal-product-name').innerText = name;
        modal.querySelector('.modal-product-option').innerText = option;
        modal.style.display = 'flex';
    }

    function toggleExchangeAddress(isAuto) {
        document.getElementById('exchange-auto-info').style.display = isAuto ? 'block' : 'none';
        document.getElementById('exchange-address-wrap').style.display = isAuto ? 'none' : 'block';
    }

    function openConfirmPurchaseModal(productId, name, option) {
        let modal = document.getElementById('confirm-modal');
        modal.querySelector('input[name="order_product_id"]').value = productId;
        modal.querySelector('.modal-product-name').innerText = name;
        modal.querySelector('.modal-product-option').innerText = option;
        modal.style.display = 'flex';
        setRating(5); // Default rating is 5 stars
    }

    function setRating(rating) {
        document.getElementById('rating-value').value = rating;
        let stars = document.querySelectorAll('#confirm-modal .star-btn');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }

    function openQnaModal(productId, name, option) {
        let modal = document.getElementById('qna-modal');
        modal.querySelector('input[name="order_product_id"]').value = productId;
        modal.querySelector('.modal-product-name').innerText = name;
        modal.querySelector('.modal-product-option').innerText = option;
        modal.style.display = 'flex';
    }
</script>
@endsection
