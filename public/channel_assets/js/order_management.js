/**
 * 주문 관리 JS
 * 주문 작업에 대한 모달 데이터 채우기 및 AJAX 폼 제출 처리.
 */

// Helper to format numbers with commas
function numberFormat(val) {
    if (val === undefined || val === null) return "0";
    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function openOrderModal(modalId, orderData) {
    var $modal = $(".popup_bx[data-id='" + modalId + "']");
    if ($modal.length === 0) return;

    $modal.stop().fadeIn(300);
    $modal.scrollTop(0);
    $("body").addClass("scroll_lock");

    // Store globally
    window.currentOrder = orderData;

    // Fill common field order_id
    $modal.find("input[name='order_id']").val(orderData.id);

    // If it's a detail popup, populate all of them
    if (modalId === 'pop1_1' || modalId === 'pop1_3' || modalId === 'pop1_4' || modalId === 'pop1_5') {
        populateAllDetailPopups(orderData);
        return;
    }

    var prefix = "";
    if (modalId === 'pop1_2_6') prefix = "cancel";
    else if (modalId === 'pop1_2_4') prefix = "return";
    else if (modalId === 'pop1_2_5') prefix = "exchange";
    else if (modalId === 'pop1_2_3') prefix = "shipping";

    if (prefix) {
        $modal.find("#" + prefix + "_order_no").text(orderData.order_no);
        $modal.find("#" + prefix + "_order_date").text(orderData.created_at);
        $modal.find("#" + prefix + "_shop_name").text(orderData.shop_name);
        $modal.find("#" + prefix + "_payment_date").text(orderData.payment_date || orderData.created_at);

        // Populate order items checklist
        var $tbody = $modal.find("#" + prefix + "_order_items");
        $tbody.empty();

        if (orderData.items && orderData.items.length > 0) {
            orderData.items.forEach(function (item) {
                var row = `
                    <tr>
                        <td><input type="checkbox" name="item_ids[]" value="${item.id}" checked></td>
                        <td>${item.status_label || item.status}</td>
                        <td class="t_l"><span class="fcol2">${item.product_name}</span></td>
                        <td>${item.product_code}</td>
                        <td>${item.option_name}</td>
                        <td>${item.qty}</td>
                        ${prefix === 'exchange' ? `
                        <td>
                             <select name="exchange_options[${item.id}]">
                                 <option value="same" selected>동일 옵션</option>
                             </select>
                        </td>
                        ` : ''}
                    </tr>
                `;
                $tbody.append(row);
            });
        }
    }
}

// Populate details modals dynamically
function populateAllDetailPopups(orderData) {
    // 1. pop1_1 (주문 정보)
    $("#pop_info_order_no").text(orderData.order_no);
    $("#pop_info_order_date").text(orderData.created_at);
    $("#pop_info_shop_name").text(orderData.shop_name);
    $("#pop_info_payment_date").text(orderData.payment_date || orderData.created_at);
    $("#pop_info_orig_order_no").text(orderData.order_no);
    
    // User Email / Info
    $("#pop_info_user_email").html(`<a class="fcol2 link" href="mailto:${orderData.user?.email || ''}">${orderData.user?.email || ''}</a> (${orderData.user_id || '비회원'})`);
    $("#pop_info_user_name").text(orderData.name);
    $("#pop_info_user_mobile").text(orderData.mobile);
    $("#pop_info_user_email2").html(`<a class="fcol2 link" href="mailto:${orderData.email || ''}">${orderData.email || ''}</a>`);
    
    // Recipient
    $("#pop_info_recipient_name").text(orderData.recipient_name || orderData.name);
    $("#pop_info_recipient_mobile").text(orderData.recipient_mobile || orderData.mobile);
    $("#pop_info_recipient_address").text('[' + (orderData.recipient_zipcode || '') + '] ' + (orderData.recipient_address || '') + ' ' + (orderData.recipient_address_detail || ''));
    
    // Payments
    $("#pop_info_total_sale_price").text(numberFormat(orderData.total_sale_price) + " 원");
    $("#pop_info_total_product_price").text(numberFormat(orderData.total_product_price) + " 원");
    $("#pop_info_total_profit").text(numberFormat(orderData.total_profit) + " 원");
    $("#pop_info_total_selling_profit").text(numberFormat(orderData.total_selling_profit) + " 원");
    $("#pop_info_delivery_fee").text(numberFormat(orderData.delivery_fee) + " 원");
    $("#pop_info_used_point").text(numberFormat(orderData.used_point) + " p");
    $("#pop_info_earned_point").text(numberFormat(orderData.earned_point) + " p");
    $("#pop_info_total_payment_price").text(numberFormat(orderData.total_payment_price) + " 원");
    $("#pop_info_payment_method").text(orderData.payment_method || '카드');

    // Items list pop1_1
    var $infoTbody = $("#pop_info_order_items_body");
    $infoTbody.empty();
    if (orderData.items && orderData.items.length > 0) {
        orderData.items.forEach(function(item) {
            var row = `
                <tr>
                    <td>${item.product_type || '자사'}</td>
                    <td class="t_l"><span class="fcol2">${item.product_name}</span></td>
                    <td>${item.product_code}</td>
                    <td>${item.option_name}</td>
                    <td>${item.qty}</td>
                    <td>${item.status.includes('Cancel') || item.status.includes('취소') ? item.qty : 0}</td>
                    <td>${item.status.includes('Return') || item.status.includes('반품') ? item.qty : 0}</td>
                    <td>${item.status.includes('Exchange') || item.status.includes('교환') ? item.qty : 0}</td>
                    <td class="t_r">${numberFormat(item.price * item.qty)} 원</td>
                    <td class="t_r">${numberFormat(item.price * item.qty)} 원</td>
                    <td class="t_r">0 원</td>
                    <td class="t_r">0 원</td>
                    <td class="t_r">0 원</td>
                    <td class="t_r">0 p</td>
                    <td class="t_r"><span class="bold fcol4">${numberFormat(item.price * item.qty)} 원</span></td>
                    <td class="t_r">0 p</td>
                    <td>${item.status_label || item.status}</td>
                    <td>${item.courier_name || '-'}</td>
                    <td>${item.tracking_number || '-'}</td>
                </tr>
            `;
            $infoTbody.append(row);
        });
    }

    // 2. pop1_3 (취소 주문 정보)
    var cancelClaim = orderData.claims_data ? orderData.claims_data.find(c => c.type === 'cancel') : null;
    $("#pop_cancel_order_no").text(orderData.order_no);
    $("#pop_cancel_claim_no").text(cancelClaim ? 'C-' + cancelClaim.id : '-');
    $("#pop_cancel_request_date").text(cancelClaim ? cancelClaim.created_at : '-');
    $("#pop_cancel_reason").text(cancelClaim ? cancelClaim.reason : '-');
    $("#pop_cancel_refund_amount").text(numberFormat(orderData.total_payment_price) + " 원");
    $("#pop_cancel_refund_account").text(cancelClaim && cancelClaim.detail_reason ? cancelClaim.detail_reason : '기존 결제 수단 환불');
    $("#pop_cancel_shop_name").text(orderData.shop_name);

    // Items list pop1_3
    var $cancelTbody = $("#pop_cancel_order_items_body");
    $cancelTbody.empty();
    if (orderData.items && orderData.items.length > 0) {
        orderData.items.forEach(function(item) {
            var row = `
                <tr>
                    <td><input type="checkbox" name="item_ids[]" value="${item.id}" checked></td>
                    <td>${item.status_label || item.status}</td>
                    <td class="t_l"><span class="fcol2">${item.product_name}</span></td>
                    <td>${item.product_code}</td>
                    <td>${item.option_name}</td>
                    <td>${item.qty}</td>
                    <td>${item.qty}</td>
                    <td>0</td>
                    <td>0</td>
                    <td class="t_r">${numberFormat(item.price * item.qty)} 원</td>
                    <td class="t_r">${numberFormat(item.price * item.qty)} 원</td>
                    <td class="t_r">0 원</td>
                    <td class="t_r">0 원</td>
                    <td class="t_r">0 원</td>
                </tr>
            `;
            $cancelTbody.append(row);
        });
    }

    // 3. pop1_4 (반품 주문 정보)
    var returnClaim = orderData.claims_data ? orderData.claims_data.find(c => c.type === 'return') : null;
    $("#pop_return_order_no").text(orderData.order_no);
    $("#pop_return_claim_no").text(returnClaim ? 'R-' + returnClaim.id : '-');
    $("#pop_return_shop_name").text(orderData.shop_name);
    $("#pop_return_request_date").text(returnClaim ? returnClaim.created_at : '-');
    $("#pop_return_method").text(returnClaim && returnClaim.detail_reason && returnClaim.detail_reason.includes('회수방법') ? returnClaim.detail_reason.split(']')[0].replace('[회수방법: ', '') : '자동 회수');
    $("#pop_return_reason").text(returnClaim ? returnClaim.reason : '-');
    $("#pop_return_address").text(returnClaim && returnClaim.detail_reason && returnClaim.detail_reason.includes('주소:') ? returnClaim.detail_reason.split('주소:')[1].split('|')[0].trim() : '-');
    $("#pop_return_payment_amount").text(numberFormat(orderData.total_payment_price) + " 원");
    $("#pop_return_deduction").text("0 원");
    $("#pop_return_refund_amount").text(numberFormat(orderData.total_payment_price) + " 원");
    $("#pop_return_refund_account").text(returnClaim && returnClaim.detail_reason && returnClaim.detail_reason.includes('상세사유:') ? returnClaim.detail_reason.split('상세사유:')[1].trim() : '기존 결제 수단 환불');

    // Items list pop1_4
    var $returnTbody = $("#pop_return_order_items_body");
    $returnTbody.empty();
    if (orderData.items && orderData.items.length > 0) {
        orderData.items.forEach(function(item) {
            var row = `
                <tr>
                    <td><input type="checkbox" name="item_ids[]" value="${item.id}" checked></td>
                    <td>-</td>
                    <td>${item.status_label || item.status}</td>
                    <td class="t_l"><span class="fcol2">${item.product_name}</span></td>
                    <td>${item.product_code}</td>
                    <td>${item.option_name}</td>
                    <td>${item.qty}</td>
                    <td>0</td>
                    <td>${item.qty}</td>
                    <td>0</td>
                    <td class="t_r">${numberFormat(item.price * item.qty)} 원</td>
                    <td class="t_r">${numberFormat(item.price * item.qty)} 원</td>
                </tr>
            `;
            $returnTbody.append(row);
        });
    }

    // 4. pop1_5 (교환 주문 정보)
    var exchangeClaim = orderData.claims_data ? orderData.claims_data.find(c => c.type === 'exchange') : null;
    $("#pop_exchange_order_no").text(orderData.order_no);
    $("#pop_exchange_claim_no").text(exchangeClaim ? 'E-' + exchangeClaim.id : '-');
    $("#pop_exchange_shop_name").text(orderData.shop_name);
    $("#pop_exchange_request_date").text(exchangeClaim ? exchangeClaim.created_at : '-');
    $("#pop_exchange_reason").text(exchangeClaim ? exchangeClaim.reason : '-');
    $("#pop_exchange_address").text(exchangeClaim && exchangeClaim.detail_reason && exchangeClaim.detail_reason.includes('주소:') ? exchangeClaim.detail_reason.split('주소:')[1].split('|')[0].trim() : '-');
    $("#pop_exchange_recipient_name").text(orderData.recipient_name || orderData.name);
    $("#pop_exchange_recipient_mobile").text(orderData.recipient_mobile || orderData.mobile);
    $("#pop_exchange_recipient_address").text('[' + (orderData.recipient_zipcode || '') + '] ' + (orderData.recipient_address || '') + ' ' + (orderData.recipient_address_detail || ''));
    $("#pop_exchange_fee").text("0 원");
    $("#pop_exchange_payment_method").text("판매처 협의");

    // Items list pop1_5
    var $exchangeTbody = $("#pop_exchange_order_items_body");
    $exchangeTbody.empty();
    if (orderData.items && orderData.items.length > 0) {
        orderData.items.forEach(function(item) {
            var row = `
                <tr>
                    <td><input type="checkbox" name="item_ids[]" value="${item.id}" checked></td>
                    <td>-</td>
                    <td>${item.status_label || item.status}</td>
                    <td class="t_l"><span class="fcol2">${item.product_name}</span></td>
                    <td>${item.product_code}</td>
                    <td>${item.option_name}</td>
                    <td>${item.option_name}</td>
                    <td>${item.qty}</td>
                    <td class="t_r">${numberFormat(item.price * item.qty)} 원</td>
                    <td class="t_r">${numberFormat(item.price * item.qty)} 원</td>
                </tr>
            `;
            $exchangeTbody.append(row);
        });
    }
}

function processOrderStatusUpdate(modalId, statusVal, confirmMsg) {
    if (confirmMsg && !confirm(confirmMsg)) return;

    if (!window.currentOrder) {
        alert("주문 정보를 찾을 수 없습니다.");
        return;
    }

    var basePopupId = "";
    if (modalId.startsWith("pop1_3")) basePopupId = "pop1_3";
    else if (modalId.startsWith("pop1_4")) basePopupId = "pop1_4";
    else if (modalId.startsWith("pop1_5")) basePopupId = "pop1_5";

    var itemIds = [];
    if (basePopupId) {
        $(".popup_bx[data-id='" + basePopupId + "'] input[name='item_ids[]']:checked").each(function() {
            itemIds.push($(this).val());
        });
    }

    if (itemIds.length === 0) {
        window.currentOrder.items.forEach(function(item) {
            itemIds.push(item.id);
        });
    }

    $.ajax({
        url: "/channel/order/status/update",
        method: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            order_id: window.currentOrder.id,
            status: statusVal,
            item_ids: itemIds
        },
        success: function(res) {
            if (res.status) {
                alert(res.message || "처리가 완료되었습니다.");
                location.reload();
            } else {
                alert(res.message || "오류가 발생했습니다.");
            }
        },
        error: function(xhr) {
            alert("오류가 발생했습니다.");
        }
    });
}

function submitOrderForm(formId, url) {
    var $form = $("#" + formId);
    var formData = new FormData($form[0]);

    if (!formData.has('_token')) {
        var token = $('meta[name="csrf-token"]').attr('content');
        if (token) formData.append('_token', token);
    }

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (response.status) {
                alert(response.message);
                location.reload();
            } else {
                alert(response.message || '오류가 발생했습니다.');
            }
        },
        error: function (xhr) {
            alert('오류가 발생했습니다: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText));
        }
    });
}

// Bind confirmation listeners
$(document).ready(function() {
    // Cancel reject
    $(document).on('click', '#btn_cancel_reject_confirm', function(e) {
        e.preventDefault();
        processOrderStatusUpdate('pop1_3_2', 'New', '취소 요청을 거부하고 이전 결제완료 상태로 되돌리시겠습니까?');
    });

    // Cancel approve
    $(document).on('click', '#btn_cancel_approve_confirm', function(e) {
        e.preventDefault();
        processOrderStatusUpdate('pop1_3_3', 'Cancelled', '취소요청을 승인하고 주문을 취소 완료하시겠습니까?');
    });

    // Return received
    $(document).on('click', '#btn_return_received_confirm', function(e) {
        e.preventDefault();
        processOrderStatusUpdate('pop1_4_2', 'Returned', '반품 회수 완료로 상태를 전환하시겠습니까?');
    });

    // Return approve
    $(document).on('click', '#btn_return_approve_confirm', function(e) {
        e.preventDefault();
        processOrderStatusUpdate('pop1_4_3', 'Returned', '반품 확정 상태로 전환하시겠습니까?');
    });

    // Return reject
    $(document).on('click', '#btn_return_reject_confirm', function(e) {
        e.preventDefault();
        processOrderStatusUpdate('pop1_4_5', 'Delivered', '반품 요청을 철회하고 배송완료 상태로 되돌리시겠습니까?');
    });

    // Exchange approve
    $(document).on('click', '#btn_exchange_approve_confirm', function(e) {
        e.preventDefault();
        processOrderStatusUpdate('pop1_5_2', 'Exchanged', '교환 요청을 승인하시겠습니까?');
    });

    // Exchange reject
    $(document).on('click', '#btn_exchange_reject_confirm', function(e) {
        e.preventDefault();
        processOrderStatusUpdate('pop1_5_4', 'Delivered', '교환 요청을 철회하고 배송완료 상태로 되돌리시겠습니까?');
    });

    // Exchange received
    $(document).on('click', '#btn_exchange_received_confirm', function(e) {
        e.preventDefault();
        processOrderStatusUpdate('pop1_5_5', 'Exchanged', '교환 상품 회수 완료로 전환하시겠습니까?');
    });

    // Exchange complete
    $(document).on('click', '#btn_exchange_complete_confirm', function(e) {
        e.preventDefault();
        processOrderStatusUpdate('pop1_5_6', 'Exchanged', '교환 확정 처리를 완료하시겠습니까?');
    });

    // Exchange to return
    $(document).on('click', '#btn_exchange_to_return_confirm', function(e) {
        e.preventDefault();
        processOrderStatusUpdate('pop1_5_8', 'Returned', '반품 상태로 전환하여 환불을 처리하시겠습니까?');
    });
});

// Modal close delegation
$(document).on('click', '.close_btn', function (e) {
    e.preventDefault();
    $(this).closest('.popup_bx').removeClass('on').fadeOut(300);
    $("body").removeClass("scroll_lock");
});
