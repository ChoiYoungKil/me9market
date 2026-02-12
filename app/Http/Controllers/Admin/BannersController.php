<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Intervention\Image\Facades\Image;

use App\Models\Banner;

class BannersController extends Controller
{
    public function banners() {
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'banners');

        $banners = Banner::get()->toArray();
        // dd($banners);

        
        return view('admin.banners.banners')->with(compact('banners'));
    }

    public function updateBannerStatus(Request $request) { // AJAX를 사용하여 배너 상태 업데이트    
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if ($data['status'] == 'Active') { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }


            Banner::where('id', $data['banner_id'])->update(['status' => $status]); 
            // echo '<pre>', var_dump($data), '</pre>';

            return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                'status'    => $status,
                'banner_id' => $data['banner_id']
            ]);
        }
    }

    public function deleteBanner($id) { // AJAX를 사용하여 서버 및 데이터베이스에서 배너 삭제    
        // `banners` 테이블에서 배너 이미지 레코드 가져오기
        $bannerImage = Banner::where('id', $id)->first();

        // 서버의 배너 이미지 경로 가져오기
        $banner_image_path = 'front/images/banner_images/';

        // 서버에서 물리적 파일 삭제
        if (file_exists($banner_image_path . $bannerImage->image)) {
            unlink($banner_image_path . $bannerImage->image);
        }

        // `banners` 테이블에서 배너 레코드 삭제
        Banner::where('id', $id)->delete();
        
        $message = '배너가 성공적으로 삭제되었습니다!';
        
        return redirect()->back()->with('success_message', $message);
    }

    public function addEditBanner(Request $request, $id = null) { // 배너 추가 또는 수정    
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'banners');


        if ($id == '') { // $id가 없으면 배너 추가
            $banner = new Banner;
            $title = '배너 이미지 추가';
            $message = '배너가 성공적으로 추가되었습니다!';
        } else { // $id가 있으면 배너 수정
            $banner = Banner::find($id);
            $title = '배너 이미지 수정';
            $message = '배너가 성공적으로 업데이트되었습니다!';
        }

        // 둘째, 요청 메소드가 'POST'인 경우, add_edit_banner.blade.php 페이지의 HTML <form>을 제출합니다 (배너 추가 또는 수정):
        if ($request->isMethod('post')) { // 추가 또는 수정 폼 제출 시
            $data = $request->all();
            // dd($data);

            $banner->type   = $data['type'];
            $banner->link   = $data['link'];
            $banner->title  = $data['title'];
            $banner->alt    = $data['alt'];
            $banner->status = 1;

            
            if ($data['type'] == 'Slider') {
                $width  = '1920';
                $height = '720';
            } else if ($data['type'] == 'Fix') {
                $width  = '1920';
                $height = '450';
            }

            // 배너 이미지 업로드    // 이미지 업로드를 위해 Intervention 패키지 사용
            if ($request->hasFile('image')) { // HTML name 속성
                $image_tmp = $request->file('image'); // 업로드된 파일 가져오기: https://laravel.com/docs/9.x/requests#retrieving-uploaded-files
                if ($image_tmp->isValid()) {
                    // 이미지 확장자 가져오기
                    $extension = $image_tmp->getClientOriginalExtension();

                    // 업로드된 이미지의 랜덤 이름 생성 (이름 중복 방지)
                    $imageName = rand(111, 99999) . '.' . $extension;

                    // 'public' 폴더 내 업로드된 이미지 경로 할당
                    $imagePath = 'front/images/banner_images/' . $imageName;

                    // Intervention 패키지를 사용하여 이미지 리사이즈 및 저장
                    Image::make($image_tmp)->resize($width, $height)->save($imagePath); 

                    // 데이터베이스 테이블에 이미지 이름 저장
                    $banner->image = $imageName;
                }
            }

            $banner->save(); // 데이터 저장 (추가 또는 수정)

            return redirect('admin/banners')->with('success_message', $message); 
        }


        return view('admin.banners.add_edit_banner')->with(compact('banner', 'title'));
    }
}