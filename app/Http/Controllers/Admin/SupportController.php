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
        $request->validate(['per_page'=>'nullable|in:20,40,60,80,100','search_type'=>'nullable|in:title,content,both','search_value'=>'nullable|string|max:100']);
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

        $notices = $query->paginate((int)$perPage)->withQueryString();

        return view('admin.support.notices')->with(compact('notices', 'perPage'));
    }

    public function addEditNotice(Request $request, $id = null)
    {
        abort_unless(in_array(\Illuminate\Support\Facades\Auth::guard('admin')->user()?->type, ['admin', 'superadmin'], true), 403);
        $editor = app(AdminPatternController::class);
        if (!$request->isMethod('post')) return $id ? $editor->editNotice((int)$id) : $editor->forms();
        if (!$request->has('status')) $request->merge(['status' => 0]);
        $response = $id ? $editor->updateNotice($request, (int)$id) : $editor->storeNotice($request);
        return $request->expectsJson() ? $response : redirect('admin/notices')->with('success_message', '공지사항이 저장되었습니다.');
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
            $request->validate(['notice_id'=>'required|integer|exists:notices,id','status'=>'required|in:노출,비노출']);
            $data = $request->all();
            if ($data['status'] == "노출") {
                $status = 0;
            } else {
                $status = 1;
            }
            Notice::findOrFail($data['notice_id'])->update(['status' => $status]);
            return response()->json(['status' => $status, 'notice_id' => $data['notice_id']]);
        }
    }

    // ==================== 자주묻는질문 (FAQ) ====================
    
    public function faqs(Request $request)
    {
        Session::put('page', 'faqs');
        $request->validate(['per_page'=>'nullable|in:20,40,60,80,100','category'=>'nullable|string|max:100','search_type'=>'nullable|in:question,answer,both','search_value'=>'nullable|string|max:100']);
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

        $faqs = $query->paginate((int)$perPage)->withQueryString();

        return view('admin.support.faqs')->with(compact('faqs', 'perPage'));
    }

    public function addEditFaq(Request $request, $id = null)
    {
        Session::put('page', 'faqs');
        
        if ($id == "") {
            $title = "자주묻는질문 추가";
            $faq = new Faq;
        } else {
            $title = "자주묻는질문 수정";
            $faq = Faq::findOrFail($id);
        }

        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'question' => 'required|string|max:500',
                'answer' => 'required|string|max:100000',
                'category'=>'nullable|string|max:100', 'order'=>'nullable|integer|min:0|max:999', 'status'=>'nullable|in:0,1',
            ];

            $customMessages = [
                'question.required' => '질문을 입력해주세요.',
                'answer.required' => '답변을 입력해주세요.',
            ];

            $this->validate($request, $rules, $customMessages);

            $faq->category = $data['category'] ?? null;
            $faq->question = $data['question'];
            $answer=app(\App\Services\RefundPolicyContent::class)->forStorage($data['answer']);
            if(trim($answer)==='')throw \Illuminate\Validation\ValidationException::withMessages(['answer'=>'답변을 입력해 주세요.']);
            $faq->answer = $answer;
            $faq->order = $data['order'] ?? 0;
            $faq->status = (string) ($data['status'] ?? '0') === '1' ? 1 : 0;
            $faq->save();

            $message = $id == "" ? "자주묻는질문이 추가되었습니다." : "자주묻는질문이 수정되었습니다.";
            return redirect('admin/faqs')->with('success_message', $message);
        }

        return view('admin.support.add_edit_faq')->with(compact('title', 'faq'));
    }

    public function deleteFaq($id)
    {
        Faq::findOrFail($id)->delete();
        return redirect()->back()->with('success_message', '자주묻는질문이 삭제되었습니다.');
    }

    public function updateFaqStatus(Request $request)
    {
        if ($request->ajax()) {
            $request->validate(['faq_id'=>'required|integer|exists:faqs,id','status'=>'required|in:활성,비활성']);
            $data = $request->all();
            if ($data['status'] == "활성") {
                $status = 0;
            } else {
                $status = 1;
            }
            Faq::findOrFail($data['faq_id'])->update(['status' => $status]);
            return response()->json(['status' => $status, 'faq_id' => $data['faq_id']]);
        }
    }

    // ==================== 제휴/문의 (Contact) ====================
    
    public function contacts(Request $request)
    {
        Session::put('page', 'contacts');
        $data=$request->validate(['keyword'=>'nullable|string|max:100','status'=>'nullable|in:pending,processing,completed','per_page'=>'nullable|in:10,20,50,100']);
        $contacts=Contact::when($request->filled('keyword'), fn($q)=>$q->where(fn($q)=>$q->where('subject','like','%'.$data['keyword'].'%')->orWhere('company','like','%'.$data['keyword'].'%')->orWhere('name','like','%'.$data['keyword'].'%')))
            ->when($request->filled('status'),fn($q)=>$q->where('status',$data['status']))->latest('id')->paginate((int)($data['per_page']??20))->withQueryString();
        return view('admin.support.contacts',compact('contacts'));
    }

    public function viewContact($id)
    {
        Session::put('page', 'contacts');
        $contact = Contact::findOrFail($id);
        return view('admin.support.view_contact',compact('contact'));
    }

    public function updateContact(Request $request, $id)
    {
        $contact=Contact::findOrFail($id);
        $data=$request->validate(['status'=>'required|in:pending,processing,completed','admin_reply'=>'nullable|string|max:10000']);
        $reply=trim($data['admin_reply']??'');
        $contact->status=$data['status'];
        if($reply!==trim($contact->admin_reply??'')) $contact->replied_at=$reply!==''?now():null;
        $contact->admin_reply=$reply!==''?$reply:null;
        $contact->save();
        return redirect('admin/contacts')->with('success_message','문의가 수정되었습니다.');
    }

    public function deleteContact($id)
    {
        Contact::findOrFail($id)->delete();
        return redirect()->back()->with('success_message', '문의가 삭제되었습니다.');
    }
}
