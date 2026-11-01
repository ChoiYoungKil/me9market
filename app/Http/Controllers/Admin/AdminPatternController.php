<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\AdminNoticeComment;
use App\Models\Notice;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Services\AdminNoticeContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminPatternController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.role:admin,superadmin');
    }

    public function detail()
    {
        $id = Order::latest('id')->value('id');

        return $id ? app(AdminOrderWorkspaceController::class)->show($id) : view('admin.workspace.empty');
    }

    public function forms(bool $popup = false, bool $modal = false)
    {
        $popup = $popup || request()->boolean('popup');
        Session::put('page', 'notices');
        $notice = new Notice;
        $notice->status = false;

        return view('admin.workspace.notice_editor', compact('notice', 'popup', 'modal'));
    }

    public function notice()
    {
        Session::put('page', 'notices');
        $notice = Notice::latest('id')->first();

        return $notice ? $this->showNotice($notice->id) : view('admin.workspace.empty');
    }

    public function showNotice(int $id)
    {
        Session::put('page', 'notices');
        $notice = Notice::findOrFail($id);
        $comments = AdminNoticeComment::where('notice_id', $id)->orderBy('id')->get();
        $popup = request()->boolean('popup');

        return view('admin.workspace.notice', compact('notice', 'comments', 'popup'));
    }

    public function editNotice(int $id)
    {
        Session::put('page', 'notices');
        $notice = Notice::findOrFail($id);
        $popup = request()->boolean('popup');
        $modal = false;

        return view('admin.workspace.notice_editor', compact('notice', 'popup', 'modal'));
    }

    public function storeNotice(Request $request)
    {
        return $this->saveNotice($request, new Notice);
    }

    public function updateNotice(Request $request, int $id)
    {
        return $this->saveNotice($request, Notice::findOrFail($id));
    }

    private function saveNotice(Request $request, Notice $notice)
    {
        $data = $request->validate(['title' => 'required|string|max:500', 'content' => 'required|string|max:100000', 'status' => 'required|in:0,1', 'is_important' => 'nullable|boolean', 'attachment' => 'nullable|file|mimes:txt,pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png|max:10240']);
        $content = app(AdminNoticeContent::class)->clean($data['content']);
        if (trim(strip_tags($content)) === '') {
            throw ValidationException::withMessages(['content' => '내용을 입력해 주세요.']);
        }
        $newFile = null;
        $old = $notice->attachment;
        try {
            if ($request->hasFile('attachment')) {
                $newFile = (string) Str::uuid().'.'.strtolower($request->file('attachment')->getClientOriginalExtension());
                $request->file('attachment')->move(public_path('admin/attachments/notices'), $newFile);
            }
            DB::transaction(function () use ($notice, $data, $content, $newFile) {
                $notice->fill(['title' => $data['title'], 'content' => $content, 'status' => (int) $data['status'], 'is_important' => (bool) ($data['is_important'] ?? false)]);
                if ($newFile) {
                    $notice->attachment = $newFile;
                }$notice->save();
            });
        } catch (\Throwable $e) {
            if ($newFile) {
                @unlink(public_path('admin/attachments/notices/'.$newFile));
            }throw $e;
        }
        if ($newFile && $old && basename($old) === $old && is_file(public_path('admin/attachments/notices/'.$old))) {
            @unlink(public_path('admin/attachments/notices/'.$old));
        }
        $request->attributes->set('admin_activity_ids', [$notice->id]);
        $url = url('admin/content/notices/'.$notice->id);
        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => '공지사항이 저장되었습니다.', 'redirect' => $url]);
        }

        return redirect($url)->with('success_message', '공지사항이 저장되었습니다.');
    }

    public function downloadNotice(int $id)
    {
        $notice = Notice::findOrFail($id);
        abort_unless($notice->attachment && basename($notice->attachment) === $notice->attachment, 404);
        $path = public_path('admin/attachments/notices/'.$notice->attachment);
        abort_unless(is_file($path), 404);

        return response()->download($path, $notice->attachment, ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function storeComment(Request $request, int $id)
    {
        $notice = Notice::findOrFail($id);
        $data = $request->validate(['content' => 'required|string|max:5000']);
        AdminNoticeComment::create(['notice_id' => $notice->id, 'admin_id' => Auth::guard('admin')->id(), 'author_name' => mb_substr(Auth::guard('admin')->user()->name, 0, 100), 'content' => trim($data['content'])]);

        return back()->with('success_message', '관리 댓글이 등록되었습니다.');
    }

    public function updateComment(Request $request, int $id, int $comment)
    {
        Notice::findOrFail($id);
        $row = AdminNoticeComment::where('notice_id', $id)->findOrFail($comment);
        $data = $request->validate(['content' => 'required|string|max:5000']);
        $row->update(['content' => trim($data['content'])]);

        return back()->with('success_message', '관리 댓글이 수정되었습니다.');
    }

    public function loading()
    {
        Session::put('page', 'dashboard');

        return view('admin.workspace.loading');
    }

    public function summary()
    {
        return response()->json(['status' => true, 'message' => '현재 주문상품 '.OrdersProduct::where('is_exchange_replacement', false)->count().'건을 조회했습니다. 조회시각 '.now()->format('Y-m-d H:i:s')]);
    }

    public function activities(Request $request)
    {
        Session::put('page', 'activities');
        $data = $request->validate(['keyword' => 'nullable|string|max:100']);
        $entries = AdminActivity::when($request->filled('keyword'), fn ($q) => $q->where(fn ($q) => $q->where('actor', 'like', '%'.$data['keyword'].'%')->orWhere('path', 'like', '%'.$data['keyword'].'%')->orWhere('metadata->summary', 'like', '%'.$data['keyword'].'%')))->latest('id')->paginate(30)->withQueryString();

        return view('admin.workspace.activities', compact('entries'));
    }

    public function activity(int $id)
    {
        $entry = AdminActivity::findOrFail($id);

        return response()->view('admin.workspace.activity_detail', compact('entry'))->header('Cache-Control', 'private, no-store');
    }
}
