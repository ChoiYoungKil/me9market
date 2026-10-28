<footer class="market-site-footer">
    <div class="market-shell">
        <div class="market-footer-top">
            <a href="{{ route('home') }}" aria-label="Me9 market 홈"><img src="{{ asset('me9market/images/common/f_logo.png') }}" alt="Me9 market" width="204" height="48"></a>
            <p>상품과 사람, 그리고 새로운 기회.</p>
            <a href="{{ route('front.member.register.member') }}">미구마켓 시작하기 <span aria-hidden="true">↗</span></a>
        </div>
        <div class="home-footer">
            <div class="home-company">
                <p class="home-footer-name">{{ config('storefront.company_name') ?: 'Me9 market' }}</p>
                <ul>
                    @foreach(['representative' => '대표자', 'business_number' => '사업자등록번호', 'commerce_number' => '통신판매업신고번호', 'address' => '주소', 'email' => '이메일'] as $key => $label)
                        @if(config('storefront.'.$key))<li><span>{{ $label }}</span> {{ config('storefront.'.$key) }}</li>@endif
                    @endforeach
                </ul>
                <p class="home-copyright">© {{ date('Y') }} {{ config('storefront.company_name') ?: 'Me9 market' }}. All rights reserved.</p>
            </div>
            <nav class="home-footer-links" aria-label="하단 메뉴">
                @if(config('shop_channel.terms_url'))<a href="{{ config('shop_channel.terms_url') }}" rel="noopener">이용약관</a>@endif
                @if(config('shop_channel.privacy_url'))<a href="{{ config('shop_channel.privacy_url') }}" rel="noopener">개인정보처리방침</a>@endif
                <a href="{{ route('cs.contact') }}">제휴·문의</a>
                <button type="button" data-market-top>BACK TO TOP <span aria-hidden="true">↑</span></button>
            </nav>
        </div>
    </div>
</footer>
