/**
 * 상품 관리 JS
 * 상품 등록을 위한 모달 데이터 채우기 및 AJAX 폼 제출 처리.
 */

function openProductRegisterModal(modalId, productData) {
    var $modal = $(".popup_bx[data-id='" + modalId + "']");
    if ($modal.length === 0) return;

    $modal.stop().fadeIn(300);
    $modal.scrollTop(0);
    $("body").addClass("scroll_lock");

    // 상품 유형에 따른 필드 채우기
    // 공통 필드
    $modal.find("input[name='product_id']").val(productData.id);

    // 모달 내 ID (예: own_product_code)
    // modalId를 기반으로 접두사 결정 필요
    var prefix = "";
    if (modalId === 'pop1_1_2') prefix = "own";
    else if (modalId === 'pop1_2_2') prefix = "public"; // 공유 상품 가정
    else if (modalId === 'pop1_3_2') prefix = "partial";
    else if (modalId === 'pop1_3_4') prefix = "partial_req";

    if (prefix) {
        $modal.find("#" + prefix + "_product_code").text(productData.code);
        $modal.find("#" + prefix + "_product_img").css("background-image", "url(" + productData.img + ")");
        $modal.find("#" + prefix + "_product_category").text(productData.category);
        $modal.find("#" + prefix + "_product_name").text(productData.name);

        $modal.find("#" + prefix + "_price_constraint").text(productData.price_constraint);
        $modal.find("#" + prefix + "_profit_constraint").text(productData.profit_constraint);
        $modal.find("#" + prefix + "_stock").text(productData.stock + " 개"); // 포맷팅
        $modal.find("#" + prefix + "_purchase_limit").text(productData.purchase_limit);
        $modal.find("#" + prefix + "_sales_period").text(productData.sales_period);
    }
}

function submitProductForm(formId, url) {
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
