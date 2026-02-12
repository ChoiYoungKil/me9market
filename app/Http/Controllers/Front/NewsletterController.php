<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\NewsletterSubscriber;

class NewsletterController extends Controller
{
    // 제출 버튼 클릭 시 front/layout/footer.blade.php에서 뉴스레터 구독 이메일 HTML 폼 제출 (AJAX 요청/호출 사용)    
    public function addSubscriber(Request $request) {
        if ($request->ajax()) { // AJAX 호출을 통한 요청인 경우
            $data = $request->all(); // AJAX 요청에서 보낸 이름/값 쌍 배열 가져오기
            // dd($data);

            $subscriberCount = NewsletterSubscriber::where('email', $data['subscriber_email'])->count(); 

            if ($subscriberCount > 0) { 
                return '이미 등록된 이메일입니다.';
            } else {
                // newsletter_subscribers 테이블에 이메일 삽입
                $subscriber = new NewsletterSubscriber;

                $subscriber->email = $data['subscriber_email'];
                $subscriber->status = 1; // 기본값 1

                $subscriber->save();


                return '이메일이 정상적으로 등록되었습니다.';
            }
        }
    }

}