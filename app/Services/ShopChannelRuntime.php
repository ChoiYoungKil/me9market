<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\Product;
use App\Models\Section;
use App\Models\ShopChannel;
use App\Models\ShopChannelNotice;
use App\Models\ShopChannelPrivateAccess;
use App\Models\ShopChannelProduct;
use App\Models\User;
use App\Models\Vendor;
use App\Support\OrderItemStatus;
use App\Services\JointPurchasePricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class ShopChannelRuntime
{
    private const CART_KEY = 'shop_channel_cart';
    private const CHANNEL_KEY = 'shop_channel_id';
    private const PRIVATE_ACCESS_KEY = 'shop_channel_private_access_id';

    public function ensureAdminLoginAccount(): Admin
    {
        $admin = Admin::where('email', 'admin@admin.com')->first();
        if (!$admin) {
            $admin = new Admin();
            $admin->name = 'Me9 전체관리자';
            $admin->type = 'admin';
            $admin->vendor_id = 0;
            $admin->mobile = '010-0000-0000';
            $admin->email = 'admin@admin.com';
            $admin->password = Hash::make('123456');
            $admin->confirm = 'Yes';
            $admin->status = 1;
            $admin->save();

            return $admin;
        }

        $dirty = false;
        if (!Hash::check('123456', $admin->password)) {
            $admin->password = Hash::make('123456');
            $dirty = true;
        }
        if ($admin->type === 'vendor') {
            $admin->type = 'admin';
            $admin->vendor_id = 0;
            $dirty = true;
        }
        if ((string) $admin->status !== '1') {
            $admin->status = 1;
            $dirty = true;
        }
        if ($admin->confirm !== 'Yes') {
            $admin->confirm = 'Yes';
            $dirty = true;
        }
        if ($dirty) {
            $admin->save();
        }

        return $admin;
    }

    public function ensureDemoData(): ShopChannel
    {
        if (!$this->canSeedDemoData()) {
            return ShopChannel::where('status', 1)->orderBy('id')->firstOrFail();
        }

        $distributor = Distributor::updateOrCreate(
            ['email' => 'partner@main.com'],
            [
                'name' => '주식회사 메인공급처',
                'password' => Hash::make('123456'),
                'phone' => '010-2222-3333',
                'status' => 1,
            ]
        );
        if (!Hash::check('123456', $distributor->password)) {
            $distributor->password = Hash::make('123456');
            $distributor->status = 1;
            $distributor->save();
        }

        $user = User::where('email', 'user@user.com')->first();
        if (!$user) {
            $user = new User();
            $user->name = 'Me9 일반회원';
            $user->username = 'user@user.com';
            $user->email = 'user@user.com';
            $user->mobile = '010-1234-5678';
            $user->password = Hash::make('123456');
            $user->status = 1;
            $user->save();
        } elseif (!Hash::check('123456', $user->password)) {
            $user->username = $user->username ?: 'user@user.com';
            $user->password = Hash::make('123456');
            $user->status = 1;
            $user->save();
        }

        $vendor = Vendor::where('email', 'john@admin.com')->first();
        if (!$vendor) {
            $vendor = new Vendor();
            $vendor->name = 'Me9 테스트 판매자';
            $vendor->mobile = '010-1111-2222';
            $vendor->email = 'john@admin.com';
            $vendor->confirm = 'Yes';
            $vendor->status = 1;
            if (Schema::hasColumn('vendors', 'commission')) {
                $vendor->commission = 10;
            }
            $vendor->save();
        } else {
            $vendor->name = $vendor->name ?: 'Me9 테스트 판매자';
            $vendor->mobile = $vendor->mobile ?: '010-1111-2222';
            $vendor->confirm = 'Yes';
            $vendor->status = 1;
            if (Schema::hasColumn('vendors', 'commission')) {
                $vendor->commission = $vendor->commission ?: 10;
            }
            $vendor->save();
        }

        $admin = Admin::where('email', 'john@admin.com')->first();
        if (!$admin) {
            $admin = new Admin();
            $admin->name = 'Me9 채널관리자';
            $admin->type = 'vendor';
            $admin->vendor_id = $vendor->id;
            $admin->mobile = '010-1111-2222';
            $admin->email = 'john@admin.com';
            $admin->password = Hash::make('123456');
            $admin->confirm = 'Yes';
            $admin->status = 1;
            $admin->save();
        } else {
            $admin->name = $admin->name ?: 'Me9 채널관리자';
            $admin->type = 'vendor';
            $admin->vendor_id = $vendor->id;
            $admin->mobile = $admin->mobile ?: '010-1111-2222';
            if (!Hash::check('123456', $admin->password)) {
                $admin->password = Hash::make('123456');
            }
            $admin->confirm = 'Yes';
            $admin->status = 1;
            $admin->save();
        }

        $this->ensureAdminLoginAccount();

        $section = Section::where('name', '라이프스타일')->first();
        if (!$section) {
            $section = new Section();
            $section->name = '라이프스타일';
            $section->status = 1;
            $section->save();
        }

        $brand = Brand::where('name', 'Me9 Select')->first();
        if (!$brand) {
            $brand = new Brand();
            $brand->name = 'Me9 Select';
            $brand->status = 1;
            $brand->save();
        }

        $category = Category::where('url', 'me9-lifestyle')->first();
        if (!$category) {
            $category = new Category();
            $category->parent_id = 0;
            $category->section_id = $section->id;
            $category->category_name = 'Me9 라이프스타일';
            $category->category_image = '';
            $category->category_discount = 0;
            $category->url = 'me9-lifestyle';
            $category->status = 1;
            $category->save();
        }

        $products = [
            ['code' => 'M9-HEADSET-001', 'name' => '노이즈 캔슬링 무선 헤드셋', 'color' => 'Black', 'price' => 89000, 'sell' => 99000, 'type' => 'own'],
            ['code' => 'M9-WATCH-002', 'name' => 'GPS 스마트 스포츠 워치', 'color' => 'Silver', 'price' => 129000, 'sell' => 159000, 'type' => 'public'],
            ['code' => 'M9-KEYBOARD-003', 'name' => '백라이트 기계식 키보드', 'color' => 'Blue Switch', 'price' => 39000, 'sell' => 49000, 'type' => 'partial'],
            ['code' => 'M9-WALLET-004', 'name' => '프리미엄 가죽 월렛', 'color' => 'Brown', 'price' => 42000, 'sell' => 59000, 'type' => 'public'],
        ];

        $shop = ShopChannel::where('channel_code', 'me9')->first();
        if (!$shop) {
            $shop = new ShopChannel();
            $shop->vendor_id = $vendor->id;
            $shop->channel_code = 'me9';
            $shop->status = 1;
            $shop->is_public = 0;
            $shop->password = 'me9';
            $shop->is_member_only = 0;
            $shop->channel_name = 'Me9 테스트 Shop 채널';
            $shop->copyright = 'Me9 Market';
            $shop->keywords = ['me9', '테스트채널', '공동구매'];
            $shop->settlement_type = 1;
            $shop->settlement_rate = 10;
            $shop->save();
        }

        foreach ($products as $productData) {
            $product = Product::where('product_code', $productData['code'])->first();
            if (!$product) {
                $product = new Product();
                $product->section_id = $section->id;
                $product->category_id = $category->id;
                $product->brand_id = $brand->id;
                $product->vendor_id = $vendor->id;
                $product->admin_id = $admin->id;
                $product->admin_type = 'vendor';
                $product->product_name = $productData['name'];
                $product->product_code = $productData['code'];
                $product->product_color = $productData['color'];
                $product->product_price = $productData['price'];
                $product->product_discount = 0;
                $product->product_weight = 1;
                $product->description = $productData['name'] . ' 상품 상세 설명입니다.';
                $product->is_featured = 'No';
                if (Schema::hasColumn('products', 'is_bestseller')) {
                    $product->is_bestseller = 'No';
                }
                $product->status = 1;
                $product->is_public = $productData['type'] === 'public' ? 'Yes' : 'No';
                $product->is_partial = $productData['type'] === 'partial' ? 'Yes' : 'No';
                $product->partial_approved = 'Approved';
                $product->distributor_id = $distributor->id;
                $product->save();
            } else {
                $product->distributor_id = $product->distributor_id ?: $distributor->id;
                $product->save();
            }

            ShopChannelProduct::firstOrCreate(
                ['shop_channel_id' => $shop->id, 'product_id' => $product->id],
                [
                    'distributor_id' => $distributor->id,
                    'product_type' => $productData['type'],
                    'approval_status' => 'approved',
                    'status' => 1,
                    'constraint_type' => 'none',
                    'stock' => 100,
                    'purchase_limit' => 10,
                    'product_price' => $productData['price'],
                    'selling_price' => $productData['sell'],
                    'profit' => $productData['sell'] - $productData['price'],
                ]
            );
        }

        $jointProducts = Product::whereIn('product_code', ['M9-HEADSET-001', 'M9-WALLET-004'])->get();
        foreach ($jointProducts as $index => $jointProduct) {
            $exists = DB::table('joint_purchases')->where('product_id', $jointProduct->id)->exists();
            if (!$exists) {
                $jointPurchaseId = DB::table('joint_purchases')->insertGetId([
                    'product_id' => $jointProduct->id,
                    'min_quantity' => $index === 0 ? 100 : 50,
                    'current_quantity' => $index === 0 ? 82 : 21,
                    'discount_price' => $index === 0 ? 79000 : 52000,
                    'start_date' => now()->subDays(3)->toDateString(),
                    'end_date' => now()->addDays($index === 0 ? 5 : 12)->toDateString(),
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                if (Schema::hasTable('joint_purchase_price_tiers')) {
                    app(JointPurchasePricingService::class)->syncTiers($jointPurchaseId, [
                        ['min_quantity' => 1, 'max_quantity' => $index === 0 ? 100 : 50, 'unit_price' => $index === 0 ? 79000 : 52000],
                        ['min_quantity' => $index === 0 ? 101 : 51, 'max_quantity' => null, 'unit_price' => $index === 0 ? 69000 : 47000],
                    ]);
                }
            }
        }

        if (ShopChannelNotice::where('shop_channel_id', $shop->id)->count() === 0) {
            ShopChannelNotice::create([
                'shop_channel_id' => $shop->id,
                'type' => 'notice',
                'author' => $shop->channel_name,
                'title' => 'Me9 테스트 Shop 채널 오픈 안내',
                'content' => '스토리보드 검증을 위한 Shop 채널이 오픈되었습니다.',
                'status' => 1,
            ]);
        }

        $this->ensureDemoOrder($shop, $distributor);

        return $shop->fresh(['activeProducts.product', 'notices']);
    }

    private function ensureDemoOrder(ShopChannel $shop, Distributor $distributor): void
    {
        $hasOrders = OrdersProduct::where('shop_channel_id', $shop->id)
            ->where('distributor_id', $distributor->id)
            ->exists();

        if ($hasOrders) {
            return;
        }

        $shopProducts = ShopChannelProduct::with('product')
            ->where('shop_channel_id', $shop->id)
            ->where('distributor_id', $distributor->id)
            ->where('status', 1)
            ->take(2)
            ->get();

        if ($shopProducts->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($shop, $distributor, $shopProducts) {
            $grandTotal = 0;
            foreach ($shopProducts->values() as $index => $shopProduct) {
                $qty = $index + 1;
                $grandTotal += (float) ($shopProduct->selling_price ?: $shopProduct->product?->product_price ?: 0) * $qty;
            }

            $order = new Order();
            $order->user_id = 0;
            $order->name = '홍길동';
            $order->address = '서울특별시 마포구 월드컵북로 396';
            $order->city = '서울특별시';
            $order->state = '마포구';
            $order->country = '대한민국';
            $order->pincode = '03925';
            $order->mobile = '010-1234-5678';
            $order->email = 'guest@me9.local';
            $order->shipping_charges = 0;
            $order->coupon_code = '';
            $order->coupon_amount = 0;
            $order->order_status = 'Payment Captured';
            $order->payment_method = 'Card';
            $order->payment_gateway = 'Me9 Mock Payment';
            $order->grand_total = $grandTotal;
            $order->save();

            foreach ($shopProducts as $index => $shopProduct) {
                $product = $shopProduct->product;
                if (!$product) {
                    continue;
                }

                $qty = $index + 1;
                $sellingPrice = (float) ($shopProduct->selling_price ?: $product->product_price);
                $status = $index === 0 ? OrderItemStatus::READY_TO_SHIP : OrderItemStatus::SHIPPING;

                $item = new OrdersProduct([
                    'order_id' => $order->id,
                    'user_id' => 0,
                    'vendor_id' => $shop->vendor_id,
                    'shop_channel_id' => $shop->id,
                    'shop_channel_product_id' => $shopProduct->id,
                    'admin_id' => $product->admin_id ?? 0,
                    'product_id' => $product->id,
                    'distributor_id' => $distributor->id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->product_name,
                    'product_color' => $product->product_color ?: '-',
                    'product_size' => '기본옵션',
                    'product_price' => $sellingPrice,
                    'supply_price' => $shopProduct->product_price ?: $product->product_price,
                    'selling_price' => $sellingPrice,
                    'product_qty' => $qty,
                    'line_total' => $sellingPrice * $qty,
                    'item_status' => OrderItemStatus::label($status),
                    'status_code' => $status,
                    'commission' => round($sellingPrice * $qty * 0.1),
                    'settlement_status' => 'pending',
                ]);

                if ($status === OrderItemStatus::SHIPPING) {
                    $item->courier_name = 'CJ대한통운';
                    $item->tracking_number = '123456789012';
                    $item->shipped_at = now();
                }

                $item->save();
            }
        });
    }

    public function seedDemoDataIfAllowed(): ?ShopChannel
    {
        return $this->canSeedDemoData() ? $this->ensureDemoData() : null;
    }

    public function currentChannel(): ShopChannel
    {
        $this->seedDemoDataIfAllowed();

        $shop = null;
        if (Session::has(self::CHANNEL_KEY)) {
            $shop = ShopChannel::find(Session::get(self::CHANNEL_KEY));
        }

        if (!$shop) {
            $shop = ShopChannel::where('channel_code', 'me9')
                ->where('status', 1)
                ->first()
                ?: ShopChannel::where('status', 1)->orderBy('id')->firstOrFail();
            Session::put(self::CHANNEL_KEY, $shop->id);
        }

        return $shop;
    }

    public function enterChannel(string $entryCode, ?string $phone = null): ?ShopChannel
    {
        $this->seedDemoDataIfAllowed();

        $shop = ShopChannel::where('channel_code', $entryCode)->first()
            ?: ShopChannel::where('password', $entryCode)->first();

        if (!$shop || (int) $shop->status !== 1) {
            return null;
        }

        if ((int) $shop->is_public === 0) {
            $access = $this->privateAccess($shop, (string) $phone, $entryCode);
            $usesLegacyPassword = trim((string) $shop->password) !== '' && hash_equals((string) $shop->password, trim($entryCode));
            if (!$access && !$usesLegacyPassword) {
                return null;
            }

            if ($access) {
                $access->forceFill([
                    'first_accessed_at' => $access->first_accessed_at ?: now(),
                    'access_count' => (int) $access->access_count + 1,
                ])->save();
                Session::put(self::PRIVATE_ACCESS_KEY, $access->id);
            } else {
                Session::forget(self::PRIVATE_ACCESS_KEY);
            }
        } else {
            Session::forget(self::PRIVATE_ACCESS_KEY);
        }

        Session::put(self::CHANNEL_KEY, $shop->id);

        return $shop;
    }

    public function enterPrivateChannel(string $phone, string $entryCode): ?ShopChannel
    {
        $this->seedDemoDataIfAllowed();

        $normalizedPhone = ShopChannelPrivateAccess::normalizePhone($phone);
        if ($normalizedPhone === '' || trim($entryCode) === '') {
            return null;
        }

        $access = ShopChannelPrivateAccess::with('shopChannel')
            ->where('phone_normalized', $normalizedPhone)
            ->where('entry_code', trim($entryCode))
            ->whereHas('shopChannel', fn ($query) => $query->where('status', 1)->where('is_public', 0))
            ->first();

        if (!$access || !$access->shopChannel) {
            return null;
        }

        $access->forceFill([
            'first_accessed_at' => $access->first_accessed_at ?: now(),
            'access_count' => (int) $access->access_count + 1,
        ])->save();

        Session::put(self::PRIVATE_ACCESS_KEY, $access->id);
        Session::put(self::CHANNEL_KEY, $access->shopChannel->id);

        return $access->shopChannel;
    }

    private function privateAccess(ShopChannel $shop, string $phone, string $entryCode): ?ShopChannelPrivateAccess
    {
        $normalizedPhone = ShopChannelPrivateAccess::normalizePhone($phone);
        if ($normalizedPhone === '') {
            return null;
        }

        return ShopChannelPrivateAccess::where('shop_channel_id', $shop->id)
            ->where('phone_normalized', $normalizedPhone)
            ->where('entry_code', trim($entryCode))
            ->first();
    }

    public function canSeedDemoData(): bool
    {
        return (bool) config('shop_channel.seed_demo_data', false);
    }

    public function products(?string $type = null)
    {
        $shop = $this->currentChannel();
        $query = ShopChannelProduct::with(['product.images', 'shopChannel'])
            ->where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->where('approval_status', 'approved');

        if ($type) {
            $query->where('product_type', $type);
        }

        return $query->orderBy('id')->get();
    }

    public function cartItems(): array
    {
        $shop = $this->currentChannel();
        $cart = Session::get(self::CART_KEY, []);
        $ids = array_keys($cart);
        $products = ShopChannelProduct::with(['product.images', 'shopChannel'])
            ->whereIn('id', $ids)
            ->where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->where('approval_status', 'approved')
            ->get()
            ->keyBy('id');

        $items = [];
        foreach ($cart as $id => $row) {
            if (!$products->has($id)) {
                unset($cart[$id]);
                continue;
            }

            $shopProduct = $products[$id];
            $qty = max(1, (int) ($row['qty'] ?? 1));
            $jointPrice = app(JointPurchasePricingService::class)->projectedPriceForProduct((int) $shopProduct->product_id, $qty);
            $price = (float) ($jointPrice['unit_price'] ?? ($shopProduct->selling_price ?: $shopProduct->product_price));
            $items[] = [
                'id' => $id,
                'shop_product' => $shopProduct,
                'product' => $shopProduct->product,
                'joint_purchase' => $jointPrice['joint_purchase'] ?? null,
                'joint_price_tier_id' => $jointPrice['tier_id'] ?? null,
                'projected_joint_quantity' => $jointPrice['projected_quantity'] ?? null,
                'option' => $row['option'] ?? '기본옵션',
                'qty' => $qty,
                'price' => $price,
                'line_total' => $price * $qty,
            ];
        }

        Session::put(self::CART_KEY, $cart);

        return $items;
    }

    public function totals(): array
    {
        $subtotal = array_sum(array_column($this->cartItems(), 'line_total'));
        $shipping = $subtotal > 0 && $subtotal < 30000 ? 2500 : 0;

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
        ];
    }

    public function addToCart(int $shopProductId, int $qty = 1, string $option = '기본옵션'): void
    {
        $shop = $this->currentChannel();

        $shopProduct = ShopChannelProduct::where('id', $shopProductId)
            ->where('shop_channel_id', $shop->id)
            ->where('status', 1)
            ->where('approval_status', 'approved')
            ->firstOrFail();

        $cart = Session::get(self::CART_KEY, []);
        $cart[$shopProduct->id] = [
            'qty' => ($cart[$shopProduct->id]['qty'] ?? 0) + max(1, $qty),
            'option' => $option ?: '기본옵션',
        ];

        Session::put(self::CART_KEY, $cart);
    }

    public function removeFromCart(int $shopProductId): void
    {
        $cart = Session::get(self::CART_KEY, []);
        unset($cart[$shopProductId]);
        Session::put(self::CART_KEY, $cart);
    }

    public function checkout(Request $request): Order
    {
        $shop = $this->currentChannel();
        $items = $this->cartItems();
        if (empty($items)) {
            abort(422, '장바구니가 비어 있습니다.');
        }

        $totals = $this->totals();

        return DB::transaction(function () use ($request, $shop, $items, $totals) {
            $order = new Order();
            $order->user_id = Auth::id() ?: 0;
            $order->name = $request->input('name', Auth::user()->name ?? '비회원');
            $order->address = $request->input('address', '서울특별시 중구 세종대로 110');
            $order->city = $request->input('city', '서울특별시');
            $order->state = $request->input('state', '중구');
            $order->country = '대한민국';
            $order->pincode = $request->input('pincode', '04524');
            $order->mobile = $request->input('mobile', '010-0000-0000');
            $order->email = $request->input('email', Auth::user()->email ?? 'guest@me9.local');
            $order->shipping_charges = $totals['shipping'];
            $order->coupon_code = '';
            $order->coupon_amount = 0;
            $order->order_status = 'Payment Captured';
            $order->payment_method = $request->input('payment_method', 'Card');
            $order->payment_gateway = 'Me9 Mock Payment';
            $order->grand_total = $totals['total'];
            $order->save();

            $jointPurchaseIds = [];
            $createdItems = collect();
            foreach ($items as $item) {
                $shopProduct = $item['shop_product'];
                $product = $item['product'];
                $status = OrderItemStatus::PAID;
                $originalPrice = (float) ($shopProduct->selling_price ?: $shopProduct->product_price);
                $isJointPurchase = !empty($item['joint_purchase']);
                $orderItem = OrdersProduct::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'vendor_id' => $shop->vendor_id,
                    'shop_channel_id' => $shop->id,
                    'shop_channel_product_id' => $shopProduct->id,
                    'joint_purchase_id' => $item['joint_purchase']->id ?? null,
                    'joint_price_tier_id' => $item['joint_price_tier_id'] ?? null,
                    'admin_id' => $product->admin_id ?? 0,
                    'product_id' => $product->id,
                    'distributor_id' => $shopProduct->distributor_id ?: $product->distributor_id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->product_name,
                    'product_color' => $product->product_color ?: '-',
                    'product_size' => $item['option'],
                    'product_price' => $item['price'],
                    'supply_price' => $shopProduct->product_price ?: $product->product_price,
                    'selling_price' => $item['price'],
                    'original_unit_price' => $isJointPurchase ? $originalPrice : null,
                    'original_line_total' => $isJointPurchase ? round($originalPrice * $item['qty'], 2) : null,
                    'repriced_unit_price' => $isJointPurchase ? $item['price'] : null,
                    'repriced_line_total' => $isJointPurchase ? $item['line_total'] : null,
                    'reprice_adjustment_amount' => $isJointPurchase ? round(($originalPrice * $item['qty']) - $item['line_total'], 2) : 0,
                    'reprice_status' => $isJointPurchase && $originalPrice != $item['price'] ? 'pending_repayment' : null,
                    'product_qty' => $item['qty'],
                    'line_total' => $item['line_total'],
                    'item_status' => OrderItemStatus::label($status),
                    'status_code' => $status,
                    'commission' => round($item['line_total'] * 0.1),
                    'settlement_status' => 'pending',
                ]);
                $createdItems->push($orderItem);

                if ($isJointPurchase) {
                    $jointPurchaseIds[] = (int) $item['joint_purchase']->id;
                }
            }

            foreach (array_unique($jointPurchaseIds) as $jointPurchaseId) {
                app(JointPurchasePricingService::class)->repricePurchase($jointPurchaseId);
            }

            if (Session::has(self::PRIVATE_ACCESS_KEY)) {
                ShopChannelPrivateAccess::where('id', Session::get(self::PRIVATE_ACCESS_KEY))
                    ->where('shop_channel_id', $shop->id)
                    ->increment('purchase_count');
            }

            if ($shop->use_purchase_sms) {
                DB::afterCommit(fn () => app(ShopChannelSmsService::class)->send(
                    $shop,
                    $order,
                    $createdItems->first(),
                    ShopChannelSmsService::TYPE_PURCHASE
                ));
            }

            Session::forget(self::CART_KEY);
            Session::put('last_shop_order_id', $order->id);
            Session::put('nonmember_order_id', $order->id);

            return $order;
        });
    }
}
