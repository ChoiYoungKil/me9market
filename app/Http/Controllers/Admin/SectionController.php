<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\Section;

class SectionController extends Controller
{
    public function sections() {
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'sections');


        // $sections = Section::get(); // Eloquent Collection
        $sections = Section::get()->toArray(); // 모든 섹션 정보를 배열로 가져오기
        // dd($sections);

        return view('admin.sections.sections')->with(compact('sections'));
    }

    public function updateSectionStatus(Request $request) { // AJAX를 사용하여 섹션 상태 업데이트
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if ($data['status'] == 'Active') { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }


            Section::where('id', $data['section_id'])->update(['status' => $status]); 
            // echo '<pre>', var_dump($data), '</pre>';

            return response()->json([ 
                'status'     => $status,
                'section_id' => $data['section_id']
            ]);
        }
    }

    public function deleteSection($id) { 
        Section::where('id', $id)->delete();
        
        $message = '섹션이 성공적으로 삭제되었습니다!';
        
        return redirect()->back()->with('success_message', $message);
    }

    public function addEditSection(Request $request, $id = null) { // 섹션 추가 또는 수정
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'sections');


        if ($id == '') { // $id가 없으면 섹션 추가
            $title = '섹션 추가';
            $section = new Section();
            // dd($section);
            $message = '섹션이 성공적으로 추가되었습니다!';
        } else { // $id가 있으면 섹션 수정
            $title = '섹션 수정';
            $section = Section::find($id);
            // dd($section);
            $message = '섹션이 성공적으로 업데이트되었습니다!';
        }

        if ($request->isMethod('post')) { // 폼 제출인 경우 (추가 또는 수정 공용)
            $data = $request->all();
            // dd($data);

            // 라라벨 유효성 검사
            $rules = [
                'section_name' => 'required|regex:/^[\pL\s\-]+$/u', 
            ];

            $customMessages = [ 
                'section_name.required' => '섹션명을 입력해 주세요',
                'section_name.regex'    => '유효한 섹션명을 입력해 주세요',
            ];

            $this->validate($request, $rules, $customMessages);

            
            // 정보 저장 또는 업데이트
            $section->name   = $data['section_name']; 
            $section->status = 1;  
            $section->save(); // 데이터베이스에 저장


            return redirect('admin/products')->with('success_message', $message);
        }


        return view('admin.sections.add_edit_section')->with(compact('title', 'section'));
    }
}