<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminBulkController extends Controller
{
    public function update(Request $request, string $type)
    {
        abort_unless(in_array($type, ['products', 'users'], true), 404);
        $data = $request->validate(['ids' => 'required|array|min:1|max:100', 'ids.*' => 'required|integer|distinct|min:1', 'status' => 'required|in:0,1']);
        DB::transaction(function () use ($data, $type) {
            $model = $type === 'products' ? Product::class : User::class;
            $records = $model::whereIn('id', $data['ids'])->orderBy('id')->lockForUpdate()->get();
            if ($records->count() !== count($data['ids'])) {
                throw ValidationException::withMessages(['ids' => '선택한 자료가 변경되었거나 존재하지 않습니다. 새로 조회해 주세요.']);
            }
            if ($type === 'users' && DB::table('member_withdrawals')->whereIn('user_id', $records->pluck('id'))->exists()) {
                throw ValidationException::withMessages(['ids' => '탈퇴 회원은 일괄 상태 변경할 수 없습니다.']);
            }
            foreach ($records as $record) {
                $record->update(['status' => (int) $data['status']]);
            }
        });

        return back()->with('success_message', count($data['ids']).'건의 상태가 적용되었습니다.');
    }
}
