<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    // GET 요청으로 Contact Us 페이지(front/pages/contact.blade.php)를 렌더링하거나 POST 요청으로 HTML 폼 제출 처리    
    public function contact(Request $request) {
        // front/pages/contact.blade.php의 HTML 폼이 제출된 경우
        if ($request->isMethod('post')) {
            $data = $request->all();


            // 유효성 검사    
            $rules = [
                // Fields/Column Names
                'name'    => 'required|string|max:100',
                'email'   => 'required|email|max:150',
                'subject' => 'required|max:200',
                'message' => 'required'
            ];

            // 각 [필드 및 유효성 검사 규칙]에 대해 라라벨의 기본 오류 메시지 커스터마이징
            $customMessages = [
                // The SAME last Fields (inside $rules array)
                'name.required'    => 'Name is required',
                'name.string'      => 'Name must be string',

                'email.required'   => 'Email is required',
                'email.email'      => 'Valid email is required',

                'subject.required' => 'Subject is requireed',

                'message.required' => 'Message is required'
            ];

            $validator = \Illuminate\Support\Facades\Validator::make($data, $rules, $customMessages);

            // dd($validator->errors()); 와 dd($validator->messages()); 는 동일합니다.
            // dd($validator->messages()); 와 dd($validator->errors()); 는 동일합니다.

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }


            // 사용자의 문의 내용을 'admin'에게 이메일로 발송
            $email = 'admin@admin.com'; // Admin's email

            // 이메일 뷰에 전달될 메시지 데이터/변수
            $messageData = [
                'name'    => $data['name'],
                'email'   => $data['email'],
                'subject' => $data['subject'],
                'comment' => $data['message']
            ];

            \Illuminate\Support\Facades\Mail::send('emails.inquiry', $messageData, function ($message) use ($email) { // 이메일 발송: 'emails.inquiry'는 resources/views/emails 폴더의 inquiry.blade.php 파일입니다.
                $message->to($email)->subject('Inquiry from a user');
            });


            // 사용자에게 성공 메시지와 함께 되돌려보냄
            $message = 'Thanks for your inquiry. We will get back to you soon.';
            return redirect()->back()->with('success_message', $message);
        }


        return view('front.pages.contact');
    }
}