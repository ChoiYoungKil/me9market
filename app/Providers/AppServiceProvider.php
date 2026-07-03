<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Customizing The Pagination View Using Bootstrap
        \Illuminate\Pagination\Paginator::useBootstrap();

        // Dynamically provide counts for the channel portal sidebar
        \Illuminate\Support\Facades\View::composer('layouts.inc.channel_sidebar', function ($view) {
            if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
                $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
                $vendor_id = $user->vendor_id;

                $view->with('sidebar_counts', [
                    'own' => \App\Models\Product::where('vendor_id', $vendor_id)->count(),
                    'public' => \App\Models\Product::where('is_public', 'Yes')->count(),
                    'selling' => \App\Models\ShopChannelProduct::whereHas('shopChannel', function($q) use ($vendor_id) {
                        $q->where('vendor_id', $vendor_id);
                    })->where('status', 1)->distinct('product_id')->count(),
                    'channels' => \App\Models\ShopChannel::where('vendor_id', $vendor_id)->count(),
                ]);

                $view->with('sidebar_order_counts', app(\App\Services\ChannelOrderMetrics::class)->counts($vendor_id));
            }
        });

        \Illuminate\Support\Facades\View::composer('layouts.channel', function ($view) {
            $headerChannels = collect();

            if (
                \Illuminate\Support\Facades\Auth::guard('admin')->check()
                && \Illuminate\Support\Facades\Schema::hasTable('shop_channels')
            ) {
                $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();

                if ($user->type === 'vendor') {
                    $headerChannels = \App\Models\ShopChannel::where('vendor_id', $user->vendor_id)
                        ->select('id', 'channel_code', 'channel_name', 'status', 'is_public', 'is_member_only')
                        ->orderByDesc('created_at')
                        ->get();
                }
            }

            $view->with('headerChannels', $headerChannels);
        });
    }
}
