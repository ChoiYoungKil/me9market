<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;

class CmsController extends Controller
{
    // GET 요청으로 Contact Us 페이지(front/pages/contact.blade.php)를 렌더링하거나 POST 요청으로 HTML 폼 제출 처리    
    // GET 요청으로 Contact Us 페이지(front/pages/contact.blade.php)를 렌더링하거나 POST 요청으로 HTML 폼 제출 처리    
    public function contact(Request $request) {
        // front/pages/contact.blade.php의 HTML 폼이 제출된 경우
        if ($request->isMethod('post')) {
            $data = $request->all();

            // 유효성 검사    
            $rules = [
                'company_name' => 'required|string|max:100',
                'manager_name' => 'required|string|max:100',
                'manager_tel_1' => 'required',
                'manager_tel_2' => 'required',
                'manager_tel_3' => 'required',
                'manager_email_1' => 'required',
                'manager_email_2' => 'required',
                'message' => 'required',
                'captcha' => 'required',
                'agree_terms' => 'required',
                'agree_privacy' => 'required',
            ];

            $customMessages = [
                'company_name.required' => '회사명을 입력해주세요.',
                'manager_name.required' => '담당자명/직책을 입력해주세요.',
                'manager_tel_2.required' => '연락처를 입력해주세요.',
                'manager_tel_3.required' => '연락처를 입력해주세요.',
                'manager_email_1.required' => '이메일을 입력해주세요.',
                'manager_email_2.required' => '이메일을 입력해주세요.',
                'message.required' => '문의내용을 입력해주세요.',
                'captcha.required' => '자동등록방지 문자를 입력해주세요.',
                'agree_terms.required' => '이용약관에 동의해주세요.',
                'agree_privacy.required' => '개인정보 수집 및 이용에 동의해주세요.',
            ];

            $validator = \Illuminate\Support\Facades\Validator::make($data, $rules, $customMessages);

            if ($validator->fails()) {
                if ($request->ajax()) {
                     return response()->json(['errors' => $validator->errors()], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            
            // 캡차 검증
            $sessionCaptcha = session('captcha_code');
            if (!$sessionCaptcha || $data['captcha'] != $sessionCaptcha) {
                 if ($request->ajax()) {
                      return response()->json(['errors' => ['captcha' => ['자동등록방지 문자가 일치하지 않습니다.']]], 422);
                 }
                 return redirect()->back()->withErrors(['captcha' => '자동등록방지 문자가 일치하지 않습니다.'])->withInput();
            }

            // DB 저장
            $contact = new Contact();
            $contact->company = $data['company_name'];
            $contact->name = $data['manager_name'];
            $contact->phone = $data['manager_tel_1'] . '-' . $data['manager_tel_2'] . '-' . $data['manager_tel_3'];
            $contact->email = $data['manager_email_1'] . '@' . $data['manager_email_2'];
            $contact->subject = '제휴/문의 - ' . $data['company_name']; // 제목 자동 생성
            $contact->message = $data['message'];
            $contact->type = 'partnership';
            $contact->save();

            // 이메일 전송 로직은 유지하거나 필요시 수정
            $email = 'admin@admin.com'; 
            $messageData = [
                'name'    => $contact->name,
                'email'   => $contact->email,
                'subject' => $contact->subject,
                'comment' => $contact->message
            ];

            /*
            \Illuminate\Support\Facades\Mail::send('emails.inquiry', $messageData, function ($message) use ($email) {
                $message->to($email)->subject('Inquiry from a user');
            });
            */
            
            // 사용자에게 성공 메시지와 함께 되돌려보냄
            $message = '문의가 성공적으로 접수되었습니다. 담당자가 확인 후 연락드리겠습니다.';
            
            // 성공 시 캡차 세션 삭제 후 재생성 (재사용 방지 및 연속 제출 지원)
            session()->forget('captcha_code');
            $newCaptcha = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 5)), 0, 10);
            session(['captcha_code' => $newCaptcha]);

            if ($request->ajax()) {
                return response()->json([
                    'success_message' => $message,
                    'new_captcha' => $newCaptcha
                ]);
            }
            return redirect()->back()->with('success_message', $message);
        }

        // 캡차 생성 (영어 대문자 10자리)
        $captcha = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 5)), 0, 10);
        session(['captcha_code' => $captcha]);

        return view('front.pages.contact', compact('captcha'));
    }
}