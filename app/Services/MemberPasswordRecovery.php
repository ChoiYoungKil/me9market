<?php

namespace App\Services;

use App\Mail\TemporaryMemberPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MemberPasswordRecovery
{
    public function issue(Request $request, array $data): ?string
    {
        $previewId = null;
        try {
            return DB::transaction(function () use ($request, $data, &$previewId) {
                $user = User::where('username', $data['username'])->where('email', $data['email'])->where('status', 1)->lockForUpdate()->first();
                if (! $user) {
                    throw ValidationException::withMessages(['email' => '일치하는 정보가 없습니다.']);
                }
                if ($user->temporary_password_issued_at?->greaterThan(now()->subMinute())) {
                    throw ValidationException::withMessages(['email' => '이미 임시비밀번호를 발급했습니다. 1분 후 다시 시도해 주세요.']);
                }
                $password = Str::password(18);
                $expires = now()->addMinutes(60);
                $mail = new TemporaryMemberPassword($password, $expires->format('Y-m-d H:i'));
                $isPreview = app()->environment('local') && in_array($request->ip(), ['127.0.0.1', '::1'], true)
                    && str_ends_with(strtolower($user->email), '.test');
                if ($isPreview) {
                    Mail::mailer('array')->to($user->email)->send($mail);
                    $previewId = (string) Str::uuid();
                    $stored = Storage::disk('local')->put('account-recovery-mail/'.$previewId.'.enc', Crypt::encryptString($mail->render()));
                    if (! $stored) { throw new \RuntimeException('검수 메일을 저장하지 못했습니다.'); }
                } else {
                    Mail::to($user->email)->send($mail);
                }
                $user->password = Hash::make($password);
                $user->remember_token = Str::random(60);
                $user->temporary_password_issued_at = now();
                $user->temporary_password_expires_at = $expires;
                $user->save();
                return $previewId;
            });
        } catch (\Throwable $exception) {
            if ($previewId) { Storage::disk('local')->delete('account-recovery-mail/'.$previewId.'.enc'); }
            throw $exception;
        }
    }
}
