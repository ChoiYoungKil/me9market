@if(data_get($shippingItem, 'recipient_name') || data_get($shippingItem, 'recipient_address'))
<p class="order-item-recipient" style="margin-top:8px;font-size:12px;overflow-wrap:anywhere;">상품별 수령인: {{ data_get($shippingItem, 'recipient_name') ?: data_get($shippingOrder, 'name') }}<br>상품별 배송지: {{ data_get($shippingItem, 'recipient_postcode') }} {{ data_get($shippingItem, 'recipient_address') ?: data_get($shippingOrder, 'address') }} {{ data_get($shippingItem, 'recipient_address_detail') }}</p>
@endif
