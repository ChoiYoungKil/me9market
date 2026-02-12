<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\Rating;

class RatingController extends Controller
{
    // 관리자 패널의 상품 평점 및 리뷰 페이지(admin/ratings/ratings.blade.php) 렌더링
    public function ratings() {
        // 사이드바의 '상품 평점 및 리뷰' 탭 활성화
        Session::put('page', 'ratings');

        $ratings = Rating::with(['user', 'product'])->get()->toArray(); // 유저 및 상품 관계와 함께 평점 정보 가져오기 (Eager Loading)
        // dd($ratings);


        return view('admin.ratings.ratings')->with(compact('ratings'));
    }

    // AJAX를 사용한 평점 상태(활성/비활성) 업데이트
    public function updateRatingStatus(Request $request) {
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if ($data['status'] == 'Active') { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }


            Rating::where('id', $data['rating_id'])->update(['status' => $status]);
            // echo '<pre>', var_dump($data), '</pre>';

            return response()->json([
                'status'    => $status,
                'rating_id' => $data['rating_id']
            ]);
        }
    }

    // AJAX를 통한 평점 삭제
    public function deleteRating($id) {
        Rating::where('id', $id)->delete();

        $message = '평점이 성공적으로 삭제되었습니다!';


        return redirect()->back()->with('success_message', $message);
    }

}