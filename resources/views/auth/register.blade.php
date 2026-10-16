<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </x-slot>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            @unless(app(\App\Services\ShopChannelRuntime::class)->canRegister())<p role="status">회원가입 약관을 준비 중입니다. 판매자에게 문의해 주세요.</p>@endunless

            <!-- Name -->
            <div>
                <x-label for="name" :value="__('Name')" />

                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-label for="email" :value="__('Email')" />

                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-label for="password" :value="__('Password')" />

                <x-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
            </div>

            @foreach(['terms_service' => ['terms_url', '이용약관'], 'terms_privacy' => ['privacy_url', '개인정보 수집 및 이용'], 'terms_third_party' => ['third_party_url', '제3자 정보제공']] as $field => [$urlKey, $label])
                <div class="mt-4"><label><input type="checkbox" name="{{ $field }}" value="1" required> {{ $label }} 동의 (필수)</label>
                @if(config('shop_channel.'.$urlKey))<a href="{{ config('shop_channel.'.$urlKey) }}" target="_blank" rel="noopener">문서 보기</a>@endif</div>
            @endforeach

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-label for="password_confirmation" :value="__('Confirm Password')" />

                <x-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required />
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ml-4" :disabled="!app(\App\Services\ShopChannelRuntime::class)->canRegister()">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
