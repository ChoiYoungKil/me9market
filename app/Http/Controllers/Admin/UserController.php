<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\User;

class UserController extends Controller
{
    // 관리자 패널의 회원 목록 페이지(admin/users/users.blade.php) 렌더링
    public function users() {
        // 사이드바 '회원 관리' 탭 활성화
        Session::put('page', 'users');


        $users = User::get()->toArray();
        // dd($users);


        return view('admin.users.users')->with(compact('users'));
    }



    // AJAX를 사용한 회원 상태(활성/비활성) 업데이트
    public function updateUserStatus(Request $request) {
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기

            if ($data['status'] == 'Active') { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }

            User::where('id', $data['user_id'])->update(['status' => $status]); 

            return response()->json([ 
                'status'  => $status,
                'user_id' => $data['user_id']
            ]);
        }
    }
}