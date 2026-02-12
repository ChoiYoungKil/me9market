<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\NewsletterSubscriber;

class NewsletterController extends Controller
{
    // 뉴스레터 구독자 목록 페이지 렌더링
    public function subscribers() {
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'subscribers');


        $subscribers = NewsletterSubscriber::get()->toArray();
        // dd($subscribers);


        return view('admin.subscribers.subscribers')->with(compact('subscribers'));
    }

    // AJAX를 통한 구독자 상태 업데이트
    public function updateSubscriberStatus(Request $request) {
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if ($data['status'] == 'Active') { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }


            NewsletterSubscriber::where('id', $data['subscriber_id'])->update(['status' => $status]); 
            // echo '<pre>', var_dump($data), '</pre>';

            return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                'status'        => $status,
                'subscriber_id' => $data['subscriber_id']
            ]);
        }
    }

    // AJAX를 통한 구독자 삭제
    public function deleteSubscriber($id) { 
        NewsletterSubscriber::where('id', $id)->delete();

        $message = '구독자가 성공적으로 삭제되었습니다!';


        return redirect()->back()->with('success_message', $message);
    }

    // Maatwebsite/Laravel Excel 패키지를 사용하여 구독자 목록을 엑셀 파일로 내보내기
    public function exportSubscribers() {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\subscribersExport, 'subscribers.xlsx'); // 구독자 엑셀 파일 다운로드
    }
}