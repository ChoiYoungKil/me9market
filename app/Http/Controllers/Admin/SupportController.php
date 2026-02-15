<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Faq;
use App\Models\Contact;
use Illuminate\Http\Request;
use Session;

class SupportController extends Controller
{
    // ==================== 공지사항 (Notice) ====================
    
    public function notices(Request $request)
    {
        Session::put('page', 'notices');
        $perPage = $request->get('per_page', 20); // 기본값 20
        
        $query = Notice::orderBy('is_important', 'desc')->orderBy('created_at', 'desc');

        // 검색
        if ($request->has('search_value') && $request->search_value != '') {
            $search_type = $request->get('search_type', 'title');
            $search_value = $request->get('search_value');

            if ($search_type == 'title') {
                $query->where('title', 'like', '%' . $search_value . '%');
            } elseif ($search_type == 'content') {
                $query->where('content', 'like', '%' . $search_value . '%');
            } else {
                // 제목 + 내용 (필요시)
                 $query->where(function($q) use ($search_value) {
                    $q->where('title', 'like', '%' . $search_value . '%')
                      ->orWhere('content', 'like', '%' . $search_value . '%');
                });
            }
        }

        $notices = $query->paginate($perPage);

        return view('admin.support.notices')->with(compact('notices', 'perPage'));
    }

    public function addEditNotice(Request $request, $id = null)
    {
        Session::put('page', 'notices');
        
        if ($id == "") {
            $title = "공지사항 추가";
            $notice = new Notice;
        } else {
            $title = "공지사항 수정";
            $notice = Notice::find($id);
        }

        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'title' => 'required',
                'content' => 'required',
            ];

            $customMessages = [
                'title.required' => '제목을 입력해주세요.',
                'content.required' => '내용을 입력해주세요.',
            ];

            $this->validate($request, $rules, $customMessages);

            $notice->title = $data['title'];
            $notice->content = $data['content'];
            $notice->is_important = isset($data['is_important']) ? 1 : 0;
            $notice->status = $data['status'] ?? 0; // 라디오 버튼 값 직접 사용

            // 파일 업로드 처리
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                
                // 기존 파일 삭제
                if (!empty($notice->attachment) && file_exists(public_path('admin/attachments/notices/' . $notice->attachment))) {
                    unlink(public_path('admin/attachments/notices/' . $notice->attachment));
                }

                // 새 파일 저장
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . rand(1000, 9999) . '.' . $extension;
                $file->move(public_path('admin/attachments/notices'), $filename);
                $notice->attachment = $filename;
            }

            $notice->save();

            $message = $id == "" ? "공지사항이 추가되었습니다." : "공지사항이 수정되었습니다.";
            return redirect('admin/notices')->with('success_message', $message);
        }

        return view('admin.support.add_edit_notice')->with(compact('title', 'notice'));
    }

    public function deleteNotice($id)
    {
        $notice = Notice::find($id);
        
        // 첨부파일 삭제
        if (!empty($notice->attachment) && file_exists(public_path('admin/attachments/notices/' . $notice->attachment))) {
            unlink(public_path('admin/attachments/notices/' . $notice->attachment));
        }
        
        $notice->delete();
        return redirect()->back()->with('success_message', '공지사항이 삭제되었습니다.');
    }

    public function deleteNoticeAttachment($id)
    {
        $notice = Notice::find($id);
        
        // 첨부파일 삭제
        if (!empty($notice->attachment) && file_exists(public_path('admin/attachments/notices/' . $notice->attachment))) {
            unlink(public_path('admin/attachments/notices/' . $notice->attachment));
        }
        
        $notice->attachment = null;
        $notice->save();
        
        return redirect()->back()->with('success_message', '첨부파일이 삭제되었습니다.');
    }

    public function updateNoticeStatus(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            if ($data['status'] == "노출") {
                $status = 0;
            } else {
                $status = 1;
            }
            Notice::where('id', $data['notice_id'])->update(['status' => $status]);
            return response()->json(['status' => $status, 'notice_id' => $data['notice_id']]);
        }
    }

    // ==================== 자주묻는질문 (FAQ) ====================
    
    public function faqs(Request $request)
    {
        Session::put('page', 'faqs');
        $perPage = $request->get('per_page', 20); // 기본값 20
        
        $query = Faq::orderBy('order', 'asc')->orderBy('created_at', 'desc');

        // 카테고리 필터
        if ($request->has('category') && $request->category != '' && $request->category != '전체') {
            $query->where('category', $request->category);
        }

        // 검색
        if ($request->has('search_value') && $request->search_value != '') {
            $search_type = $request->get('search_type', 'question');
            $search_value = $request->get('search_value');

            if ($search_type == 'question') {
                $query->where('question', 'like', '%' . $search_value . '%');
            } elseif ($search_type == 'answer') {
                $query->where('answer', 'like', '%' . $search_value . '%');
            } else {
                // 질문 + 답변
                $query->where(function($q) use ($search_value) {
                    $q->where('question', 'like', '%' . $search_value . '%')
                      ->orWhere('answer', 'like', '%' . $search_value . '%');
                });
            }
        }

        $faqs = $query->paginate($perPage);

        return view('admin.support.faqs')->with(compact('faqs', 'perPage'));
    }

    public function addEditFaq(Request $request, $id = null)
    {
        Session::put('page', 'faqs');
        
        if ($id == "") {
            $title = "FAQ 추가";
            $faq = new Faq;
        } else {
            $title = "FAQ 수정";
            $faq = Faq::find($id);
        }

        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'question' => 'required',
                'answer' => 'required',
            ];

            $customMessages = [
                'question.required' => '질문을 입력해주세요.',
                'answer.required' => '답변을 입력해주세요.',
            ];

            $this->validate($request, $rules, $customMessages);

            $faq->category = $data['category'] ?? null;
            $faq->question = $data['question'];
            $faq->answer = $data['answer'];
            $faq->order = $data['order'] ?? 0;
            $faq->status = isset($data['status']) ? 1 : 0;
            $faq->save();

            $message = $id == "" ? "FAQ가 추가되었습니다." : "FAQ가 수정되었습니다.";
            return redirect('admin/faqs')->with('success_message', $message);
        }

        return view('admin.support.add_edit_faq')->with(compact('title', 'faq'));
    }

    public function deleteFaq($id)
    {
        Faq::where('id', $id)->delete();
        return redirect()->back()->with('success_message', 'FAQ가 삭제되었습니다.');
    }

    public function updateFaqStatus(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            if ($data['status'] == "활성") {
                $status = 0;
            } else {
                $status = 1;
            }
            Faq::where('id', $data['faq_id'])->update(['status' => $status]);
            return response()->json(['status' => $status, 'faq_id' => $data['faq_id']]);
        }
    }

    // ==================== 제휴/문의 (Contact) ====================
    
    public function contacts()
    {
        Session::put('page', 'contacts');
        $contacts = Contact::orderBy('created_at', 'desc')->get();
        return view('admin.support.contacts')->with(compact('contacts'));
    }

    public function viewContact($id)
    {
        Session::put('page', 'contacts');
        $contact = Contact::find($id);
        return view('admin.support.view_contact')->with(compact('contact'));
    }

    public function updateContact(Request $request, $id)
    {
        $contact = Contact::find($id);
        
        if ($request->isMethod('post')) {
            $data = $request->all();
            
            $contact->status = $data['status'];
            $contact->admin_reply = $data['admin_reply'] ?? null;
            
            if (!empty($data['admin_reply'])) {
                $contact->replied_at = now();
            }
            
            $contact->save();
            
            return redirect('admin/contacts')->with('success_message', '문의가 수정되었습니다.');
        }
    }

    public function deleteContact($id)
    {
        Contact::where('id', $id)->delete();
        return redirect()->back()->with('success_message', '문의가 삭제되었습니다.');
    }
}
