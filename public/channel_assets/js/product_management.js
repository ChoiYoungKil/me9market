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

$(document).on('click', '.product-view-button', function () {
    openProductViewModal(JSON.parse(this.dataset.product));
});

var baseProductRequest;
$(document).on('click', '.base-product-view-button', function () {
    var $modal = $(".popup_bx[data-id='base-product-detail']");
    if (baseProductRequest) baseProductRequest.abort();
    $modal.find('[data-product-content]').prop('hidden', true);
    $modal.find('[data-product-status]').text('불러오는 중입니다.');
    $modal.stop().fadeIn(300).scrollTop(0);
    baseProductRequest = $.get(this.dataset.url).done(function (response) {
        if (!response.status) {
            $modal.find('[data-product-status]').text(response.message || '상품을 불러올 수 없습니다.');
            return;
        }
        var product = response.product;
        $modal.find('[data-field]').each(function () {
            var value = this.dataset.field === 'category_path' ? response.category_path : product[this.dataset.field];
            $(this).text(value || '-');
        });
        var $images = $modal.find('[data-product-images]').empty();
        (product.image_urls || []).forEach(function (url) {
            $('<img>').attr({src: url, alt: product.product_name}).css({maxWidth: '100%', maxHeight: '240px', objectFit: 'contain'}).appendTo($images);
        });
        var detail = new DOMParser().parseFromString(product.detail_html || '', 'text/html');
        detail.querySelectorAll('script, style').forEach(function (element) { element.remove(); });
        $modal.find('[data-product-description]').text(detail.body.textContent.trim());
        var $detailImages = $modal.find('[data-product-detail-images]').empty();
        if (product.detail_display_type === 'image') {
            [product.detail_pc_image, product.detail_mobile_image].filter(Boolean).forEach(function (file) {
                $('<img>').attr({src: '/front/images/product_detail_images/' + encodeURIComponent(file), alt: product.product_name}).css({maxWidth: '100%', height: 'auto'}).appendTo($detailImages);
            });
        }
        $modal.find('[data-product-status]').empty();
        $modal.find('[data-product-content]').prop('hidden', false);
    }).fail(function (xhr, status) {
        if (status !== 'abort') $modal.find('[data-product-status]').text('상품을 불러오지 못했습니다. 다시 시도해 주세요.');
    });
});

function openProductViewModal(productData) {
    var $modal = $(".popup_bx[data-id='pop2']");
    if ($modal.length === 0) return;

    $modal.find(".ttl").text("판매 상품 정보 (" + productData.type_label + ")");
    $modal.find("#view_product_code").text(productData.code);
    $modal.find("#view_product_link").attr('href', productData.edit_url);
    $modal.find("#view_product_img").css("background-image", "url(" + productData.img + ")");
    $modal.find("#view_product_category").text(productData.category);
    $modal.find("#view_product_name").text(productData.name);

    $modal.find("#view_price_constraint").text(productData.price_constraint);
    $modal.find("#view_profit_constraint").text(productData.profit_constraint);
    $modal.find("#view_stock").text(productData.stock_text);
    $modal.find("#view_purchase_limit").text(productData.purchase_limit);
    $modal.find("#view_sales_period").text(productData.sales_period);
    $modal.find("#view_selling_price").text(productData.selling_price + "원");

    $modal.stop().fadeIn(300);
    $modal.scrollTop(0);
}

function updateProductStatus(url, shopProductId, status, actionName) {
    if (!confirm('정말로 해당 상품을 ' + actionName + ' 하시겠습니까?')) {
        return;
    }

    var token = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            _token: token,
            shop_product_id: shopProductId,
            status: status
        },
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
