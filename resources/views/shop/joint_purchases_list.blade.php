@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
<div style="background: #f6f7f9; min-height: 100vh; padding-bottom: 50px;">
    <div style="background: #111827; color: #fff; padding: 22px 32px;">
        <div style="max-width: 1180px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
            <h1 style="margin: 0; font-size: 24px;">공동구매</h1>
            <a href="{{ route('shop.channel_main') }}" style="color: #fff; text-decoration: none; font-weight: 800;">채널 홈</a>
        </div>
    </div>

    <main style="max-width: 1180px; margin: 28px auto; padding: 0 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
            @forelse($jointPurchases as $joint)
                @include('shop.partials.joint_card', ['joint' => $joint])
            @empty
                <div style="background: #fff; border: 1px solid #d9dee7; border-radius: 8px; padding: 18px;">진행중인 공동구매가 없습니다.</div>
            @endforelse
        </div>
    </main>
</div>
@endsection
