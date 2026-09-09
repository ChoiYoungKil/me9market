<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\ShopChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ShopOrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ShopChannel $shop,
        public Order $order,
        public Collection $items
    ) {}

    public function build(): self
    {
        return $this
            ->subject('['.$this->shop->channel_name.'] 주문이 접수되었습니다.')
            ->view('emails.shop_order');
    }
}
