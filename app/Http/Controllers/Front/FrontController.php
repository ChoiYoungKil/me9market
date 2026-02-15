<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Faq;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index()
    {
        return view('front.index');
    }

    public function notice(Request $request)
    {
        $query = Notice::where('status', 1)->orderBy('is_important', 'desc')->orderBy('created_at', 'desc');

        // 검색 기능
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        $notices = $query->paginate(10);
        
        return view('front.pages.notice', compact('notices'));
    }

    public function noticeView($id)
    {
        $notice = Notice::where('status', 1)->findOrFail($id);
        
        // 조회수 증가
        $notice->increment('view_count');
        
        // 이전글/다음글
        $prevNotice = Notice::where('status', 1)
            ->where('id', '<', $id)
            ->orderBy('id', 'desc')
            ->first();
            
        $nextNotice = Notice::where('status', 1)
            ->where('id', '>', $id)
            ->orderBy('id', 'asc')
            ->first();
        
        return view('front.pages.notice_view', compact('notice', 'prevNotice', 'nextNotice'));
    }

    public function faq(Request $request)
    {
        $query = Faq::where('status', 1)->orderBy('order', 'asc')->orderBy('created_at', 'desc');

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

        $faqs = $query->paginate(10); // 한 페이지에 10개

        return view('front.pages.faq', compact('faqs'));
    }

    public function contact()
    {
        return view('front.pages.contact');
    }
}
