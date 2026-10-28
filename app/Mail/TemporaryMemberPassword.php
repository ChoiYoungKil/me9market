<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemporaryMemberPassword extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $temporaryPassword, public string $expiresAt)
    {
    }

    public function build()
    {
        return $this->subject('Me9 Market 임시비밀번호가 발급되었습니다.')
            ->view('emails.member_temporary_password', [
                'loginUrl' => route('front.member.login'),
                'logoUrl' => asset('me9market/images/common/logo.png'),
            ]);
    }
}
