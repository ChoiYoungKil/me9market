<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
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
                || ! $request->routeIs('admin.login.submit')
                || $request->expectsJson()) {
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
