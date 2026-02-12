<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Rating;


class RatingController extends Controller
{
    // 상품 상세 페이지(front/products/detail.blade.php)에서 상품 별점 및 리뷰 추가    
    public function addRating(Request $request) {
        // 로그인한 사용자만 상품 평가 가능
        if (!Auth::check()) { 
            $message = '상품을 평가하려면 로그인해 주세요.';
            return redirect()->back()->with('error_message', $message);
        }

        if ($request->isMethod('post')) {
            $data = $request->all();

            // 사용자가 이전에 이 상품을 이미 평가했는지 확인
            $user_id = Auth::user()->id; 
            $ratingCount = Rating::where([
                'user_id'    => $user_id,
                'product_id' => $data['product_id']
            ])->count();

            if ($ratingCount > 0) {
                $message = '이미 이 상품을 평가하셨습니다!';
                return redirect()->back()->with('error_message', $message);
            } else { // 별점 추가
                // 유효성 검사
                // 사용자가 별점을 선택했는지 확인
                if (empty($data['rating'])) {
                    $message = '별점을 선택해 상품을 평가해 주세요!';
                    return redirect()->back()->with('error_message', $message);
                } else {

                    $rating = new Rating();

                    $rating->user_id    = $user_id;
                    $rating->product_id = $data['product_id'];
                    $rating->review     = $data['review'];
                    $rating->rating     = $data['rating'];
                    $rating->status     = 0; // 관리자 승인 후 표시되도록 기본값을 0(비활성)으로 설정

                    $rating->save();

                    // 성공 메시지 표시
                    $message = '상품을 평가해 주셔서 감사합니다! 관리자 승인 후 반영됩니다.';
                    return redirect()->back()->with('success_message', $message);
                }
            }
        }
    }

}