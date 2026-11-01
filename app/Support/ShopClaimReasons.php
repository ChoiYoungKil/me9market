<?php

namespace App\Support;

class ShopClaimReasons
{
    public static function for(string $action): array
    {
        return ($action === 'exchange'
            ? ['wrong_product'=>'다른 상품이 배송되었습니다', 'defect'=>'상품에 문제가 있습니다', 'option'=>'다른 옵션으로 교환하고 싶습니다']
            : ['change_mind'=>'단순변심', 'wrong_product'=>'다른 상품이 배송되었습니다', 'defect'=>'상품에 문제가 있습니다']) + ['other'=>'기타'];
    }
}
