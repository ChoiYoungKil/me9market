/**
 * 주문 관리 JS
 * 주문 작업에 대한 모달 데이터 채우기 및 AJAX 폼 제출 처리.
 */

function openOrderModal(modalId, orderData) {
    // 1. 모달 열기
    // 'openModal' 또는 유사한 일반 함수가 레이아웃이나 common.js에 존재한다고 가정
    // 없다면 수동으로 표시. 템플릿은 'on' 클래스 또는 스타일된 디스플레이를 사용하는 것으로 보임.
    // 기존 코드를 기반으로 볼 때: $(".popup_bx[data-id='" + modalId + "']").addClass("on");
    // 하지만 pop_btn이 어떻게 작동하는지 확인 필요. 현재는 표준 로직 수행.

    var $modal = $(".popup_bx[data-id='" + modalId + "']");
    if ($modal.length === 0) return;

    // 기존 사이트 동작과 일치하도록 fadeIn 사용
    $modal.stop().fadeIn(300);
    $modal.scrollTop(0);
    $("body").addClass("scroll_lock");

    // 2. 공통 필드 채우기
    $modal.find("input[name='order_id']").val(orderData.id);

    // 특정 ID를 가진 텍스트 필드
    // 형식: modalId_fieldName (예: #cancel_order_no)
    // 안전을 위해 공통 필드를 수동으로 매핑

    // 접두사 추출 (예: 'modal_cancel_request' -> 'cancel')
    // 실제로는 블레이드에서 할당한 ID를 사용: header "cancel", "return", "exchange", "shipping"
    var prefix = "";
    if (modalId === 'pop1_2_6') prefix = "cancel";
    else if (modalId === 'pop1_2_4') prefix = "return";
    else if (modalId === 'pop1_2_5') prefix = "exchange";
    else if (modalId === 'pop1_2_3') prefix = "shipping";

    if (prefix) {
        $modal.find("#" + prefix + "_order_no").text(orderData.order_no);
        $modal.find("#" + prefix + "_order_date").text(orderData.created_at);
        $modal.find("#" + prefix + "_shop_name").text(orderData.shop_name);
        $modal.find("#" + prefix + "_payment_date").text(orderData.payment_date); // 선택 사항

        // 3. 주문 상품 채우기
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
                                <option value="" disabled selected>변경 옵션</option>
                                <option value="same">동일 옵션</option>
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


function submitOrderForm(formId, url) {
    var $form = $("#" + formId);
    var formData = new FormData($form[0]);

    // CSRF 토큰
    // <meta name="csrf-token" content="...">가 존재하거나 전달한다고 가정
    // 폼에 _token이 없으면 메타에서 추가
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

// 모달 닫기 이벤트 위임 (이미 처리되지 않은 경우)
$(document).on('click', '.close_btn', function (e) {
    e.preventDefault();
    $(this).closest('.popup_bx').removeClass('on');
    $("body").removeClass("scroll_lock");
});
