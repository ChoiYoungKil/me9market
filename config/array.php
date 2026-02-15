<?php

return [
    // 회원 탈퇴 사유
    'withdraw_reasons' => [
        'reason_1' => '이유 1111',
        'reason_2' => '이유 2',
        'reason_3' => '이유 3',
        'reason_4' => '이유 4',
        'reason_other' => '기타',
    ],

    // 주문 취소 사유
    'order_cancel_reasons' => [
        'simple_change' => '단순 변심(구매 의사 변경)',
        'wrong_option' => '상품 옵션/수량 잘못 선택',
        'wrong_address' => '배송지/수령인 정보 입력 오류',
        'payment_change' => '결제수단 변경(카드/계좌/간편결제 등)',
        'discount_mistake' => '할인/쿠폰/포인트 적용 실수(다시 주문하려고)',
        'reorder' => '중복 주문(같은 상품을 두 번 결제)',
        'price_change' => '주문 후 가격 변동/타 사이트 더 저렴함(가격 비교)',
        'delivery_issue' => '배송 일정이 맞지 않음(필요일 변경)',
        'other' => '기타',
    ],

    // 반품 사유
    'order_return_reasons' => [
        'simple_change' => '단순 변심(마음이 바뀜)',
        'diff_expect' => '상품이 기대와 다름(디자인/색상/재질/질감)',
        'size_issue' => '사이즈/핏이 맞지 않음(의류·신발)',
        'purpose_mismatch' => '사용 목적과 맞지 않음',
        'order_mistake' => '주문 실수(옵션/수량/상품 잘못 주문)',
        'late_delivery' => '배송이 늦어 필요 없어짐(정책상 가능할 때)',
        'other' => '기타',
    ],

    // 교환 사유
    'order_exchange_reasons' => [
        'color_change' => '색상 변경 원함',
        'option_change' => '다른 옵션/모델로 변경 원함',
        'diff_expect' => '예상과 다름(핏/착용감)',
        'other' => '기타',
    ],
];
