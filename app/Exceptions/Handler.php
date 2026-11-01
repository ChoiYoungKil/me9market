<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Psr\Log\LogLevel;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'entry_code',
        'otp',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() !== 419
                || ! $exception->getPrevious() instanceof TokenMismatchException
                || ! $request->isMethod('post')
                || $request->expectsJson()) {
                return null;
            }

            if ($request->routeIs('shop.login.submit', 'shop.gate.submit')
                || ($request->routeIs('social.register') && $request->session()->get('social_signup.context') === 'shop')) {
                $channel = $request->input('channel', $request->session()->get('shop_entry_channel_code'));
                $target = $request->routeIs('shop.gate.submit') ? 'shop.gate' : 'shop.login';

                return redirect()->route($target, is_string($channel) && strlen($channel) <= 80 ? ['channel' => $channel] : [])
                    ->with('error_message', '화면의 유효시간이 지났습니다. 다시 입력해 주세요.')
                    ->withInput($request->only('login_id', 'phone', 'save_id'));
            }
            if ($request->routeIs('mypage.channel_admin')) {
                // Restart with a fresh form; never replay a role switch or bypass CSRF.
                return redirect()->route(\Illuminate\Support\Facades\Auth::guard('web')->check()
                    ? 'mypage.dashboard' : 'front.member.login')
                    ->with('error_message', '화면의 유효시간이 지났습니다. 새로 열린 화면에서 채널관리자를 다시 선택해 주세요.');
            }
            if (! $request->routeIs('admin.login.submit')) {
                return null;
            }

            // Do not replay credentials or bypass CSRF; restart the login form with a fresh token.
            $email = $request->input('email');

            return redirect()->route('admin.login')
                ->with('error_message', '로그인 화면의 유효시간이 지났습니다. 비밀번호를 다시 입력해 주세요.')
                ->withInput(is_string($email) ? ['email' => mb_substr($email, 0, 255)] : []);
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
