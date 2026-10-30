<?php

namespace App\Services;

use App\Models\{Admin, ChannelSubAccount, Contact, Order, ShopChannel, Vendor};
use App\Support\OrderItemStatus;

class ChannelDashboard
{
    public function canView(Admin $admin, string $permission): bool
    {
        if ((int) $admin->status !== 1) return false;
        if ($admin->type === 'vendor') return true;
        if ($admin->type !== 'subadmin') return false;
        $account = ChannelSubAccount::where('admin_id', $admin->id)->where('vendor_id', $admin->vendor_id)
            ->activeNow()->first();
        return in_array($permission, $account?->permissions ?? [], true);
    }

    public function shortcuts(Admin $admin): array
    {
        $choices = [
            'shops' => ['Shop 채널목록', 'channel.shop_list'],
            'products' => ['자사상품목록', 'channel.product_own'],
            'public' => ['공개상품목록', 'channel.product_public'],
            'orders' => ['일반 주문목록', 'channel.order.list'],
            'inquiries' => ['고객 문의사항', 'channel.inquiries.index'],
            'settlements' => ['정산관리', 'channel.settlement.list'],
            'info' => ['정보관리', 'channel.info.management'],
        ];
        $permissions = ['shops'=>'shop', 'products'=>'product', 'public'=>'product', 'orders'=>'order',
            'inquiries'=>'order', 'settlements'=>'settings', 'info'=>'settings'];
        return array_filter($choices, fn ($key) => $this->canView($admin, $permissions[$key]), ARRAY_FILTER_USE_KEY);
    }

    public function data(Admin $admin): array
    {
        $vendorId = (int) $admin->vendor_id;
        $from = today()->subDays(14);
        $to = today()->endOfDay();
        $yearFrom = today()->startOfYear();
        $yearTo = today()->endOfYear();
        $vendor = Vendor::with('vendorbusinessdetails')->find($vendorId);
        $metrics = app(ChannelOrderMetrics::class);
        $canViewOrders = $this->canView($admin, 'order');
        $canViewShops = $this->canView($admin, 'shop');
        $canViewSettings = $this->canView($admin, 'settings');
        $counts = $canViewOrders ? $metrics->counts($vendorId, $from, $to) : [];
        $ownedItemIds = $metrics->items($vendorId)->select('orders_products.id');
        $recentOrders = Order::with(['orders_products' => fn ($q) => $q->whereIn('orders_products.id', clone $ownedItemIds)->with('shopChannel')])
            ->whereHas('orders_products', fn ($q) => $q->whereIn('orders_products.id', clone $ownedItemIds))
            ->orderByDesc('created_at')->orderByDesc('id')->limit(5)->get();
        $recentInquiries = Contact::where('vendor_id', $vendorId)
            ->where(fn ($q) => $q->whereNotNull('order_product_id')->orWhere(fn ($q) => $q->whereNotNull('shop_channel_id')->whereNotNull('product_id')))
            ->orderByDesc('created_at')->orderByDesc('id')->limit(5)->get();

        // Replacements fulfil the original sale; they are not additional purchases.
        $sales = $metrics->items($vendorId)
            ->when(!$canViewOrders, fn ($q) => $q->whereRaw('1 = 0'))
            ->where(fn ($q) => $q->whereNull('is_exchange_replacement')->orWhere('is_exchange_replacement', false))
            ->whereNotIn(\DB::raw("COALESCE(NULLIF(status_code, ''), item_status)"), [
                OrderItemStatus::CANCELLED, OrderItemStatus::RETURNED, 'Cancelled', 'Returned', '취소완료', '반품완료',
            ]);
        $shopSales = (clone $sales)->whereBetween('orders_products.created_at', [$from, $to])
            ->leftJoin('shop_channels', 'orders_products.shop_channel_id', '=', 'shop_channels.id')
            ->selectRaw('orders_products.shop_channel_id, shop_channels.channel_name, SUM(product_qty) as quantity')
            ->groupBy('orders_products.shop_channel_id', 'shop_channels.channel_name')->orderByDesc('quantity')->get();
        $rows = (clone $sales)->whereBetween('orders_products.created_at', [$yearFrom, $to])
            ->leftJoin('products', 'orders_products.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.id as category_id, categories.category_name, MONTH(orders_products.created_at) as month, SUM(product_qty) as quantity')
            ->groupBy('categories.id', 'categories.category_name', \DB::raw('MONTH(orders_products.created_at)'))->get();
        $categorySeries = $rows->groupBy(fn ($row) => $row->category_id ?? 'none')->map(function ($group) {
            $monthly = $group->pluck('quantity', 'month');
            return ['label' => $group->first()->category_name ?: '미분류',
                'data' => array_map(fn ($month) => (int) ($monthly[$month] ?? 0), range(1, 12))];
        })->values();
        $operatingChannels = ShopChannel::where('vendor_id', $vendorId)->where('status', 1)
            ->where(fn ($q) => $q->whereNull('closure_status')->orWhere('closure_status', '!=', 'approved'))
            ->where(fn ($q) => $q->where('use_period_type', 0)->orWhereNull('use_period_type')
                ->orWhere(fn ($q) => $q->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', now()))
                    ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()))))
            ->orderBy('channel_name')->get();
        if (!$canViewOrders) { $recentOrders = collect(); $recentInquiries = collect(); }
        if (!$canViewShops) $operatingChannels = collect();
        $shortcuts = $this->shortcuts($admin);
        $selectedShortcuts = array_values(array_intersect($admin->channel_shortcuts ?? ['shops', 'products', 'orders'], array_keys($shortcuts)));
        $company = $vendor?->vendorbusinessdetails;

        return compact('counts', 'recentOrders', 'recentInquiries', 'categorySeries', 'operatingChannels',
            'shortcuts', 'selectedShortcuts', 'vendor', 'company', 'from', 'to', 'yearFrom', 'yearTo', 'canViewOrders', 'canViewShops', 'canViewSettings') + [
            'shopSales' => ['labels' => $shopSales->map(fn ($row) => $row->channel_name ?: '채널 정보 없음')->values(),
                'data' => $shopSales->pluck('quantity')->map(fn ($qty) => (int) $qty)->values()],
        ];
    }
}
