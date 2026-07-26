<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\OrdersProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class OrderManagerController extends Controller
{
    public function index(Request $request)
    {
        Session::put('page', 'order_managers');

        $keyword = trim((string) $request->query('keyword', ''));
        $status = $request->query('status', 'all');

        $managers = Distributor::withCount('products')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('email', 'like', '%' . $keyword . '%')
                        ->orWhere('name', 'like', '%' . $keyword . '%')
                        ->orWhere('phone', 'like', '%' . $keyword . '%');
                });
            })
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('status', (int) $status);
            })
            ->withCount('ordersProducts')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.order_managers.index', compact('managers', 'keyword', 'status'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'status' => 'required|in:0,1',
            'email' => 'required|email|max:255|unique:distributors,email',
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6|max:100',
        ]);

        Distributor::create([
            'status' => (int) $data['status'],
            'email' => $data['email'],
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password'] ?? '123456'),
        ]);

        return redirect()->route('admin.order_managers.index')
            ->with('success_message', '발주사가 등록되었습니다. 비밀번호 미입력 시 기본 비밀번호는 123456입니다.');
    }

    public function update(Request $request, $id)
    {
        $manager = Distributor::findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:0,1',
            'email' => 'required|email|max:255|unique:distributors,email,' . $manager->id,
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6|max:100',
        ]);

        $payload = [
            'status' => (int) $data['status'],
            'email' => $data['email'],
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $manager->update($payload);

        return redirect()->route('admin.order_managers.index')
            ->with('success_message', '발주사 정보가 수정되었습니다.');
    }

    public function resetPassword($id)
    {
        $manager = Distributor::findOrFail($id);
        $manager->password = Hash::make('123456');
        $manager->save();

        return redirect()->route('admin.order_managers.index')
            ->with('success_message', $manager->name . ' 발주사 비밀번호를 123456으로 초기화했습니다.');
    }

    public function destroy($id)
    {
        $manager = Distributor::findOrFail($id);

        Product::where('distributor_id', $manager->id)->update([
            'distributor_id' => null,
            'order_manager_enabled' => false,
        ]);
        OrdersProduct::where('distributor_id', $manager->id)->update(['distributor_id' => null]);

        $manager->delete();

        return redirect()->route('admin.order_managers.index')
            ->with('success_message', '발주사를 삭제하고 연결된 상품/주문 배정을 해제했습니다.');
    }

    public function portal(Request $request, $id)
    {
        $destination = $request->input('destination', 'pending');
        if (!in_array($destination, ['pending', 'completed'], true)) {
            $destination = 'pending';
        }

        $manager = Distributor::findOrFail($id);

        Session::put('distributor_id', $manager->id);
        Session::put('distributor_name', $manager->name);
        Session::put('distributor_email', $manager->email);

        $route = $destination === 'completed'
            ? 'distributor.orders.completed'
            : 'distributor.orders.pending';

        return redirect()->route($route)
            ->with('flash_message_success', $manager->name . ' 발주사 페이지로 연결되었습니다.');
    }
}
