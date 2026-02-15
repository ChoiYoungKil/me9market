<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorsBusinessDetail;
use App\Models\VendorsBankDetail;

class UserController extends Controller
{
    // 관리자 패널의 회원 목록 페이지(admin/users/users.blade.php) 렌더링
    public function users(Request $request) {
        // 사이드바 '회원 관리' 탭 활성화
        Session::put('page', 'users');

        $query = User::query();

        // Search Filter
        if ($request->has('search_value') && $request->search_value != '') {
            $search_value = $request->search_value;
            $search_type = $request->search_type;

            if ($search_type == 'name') {
                $query->where('name', 'like', '%' . $search_value . '%');
            } elseif ($search_type == 'email') {
                $query->where('email', 'like', '%' . $search_value . '%');
            } elseif ($search_type == 'mobile') {
                $query->where('mobile', 'like', '%' . $search_value . '%');
            } elseif ($search_type == 'id') {
                $query->where('id', $search_value);
            } else {
                 $query->where(function($q) use ($search_value) {
                    $q->where('name', 'like', '%' . $search_value . '%')
                      ->orWhere('email', 'like', '%' . $search_value . '%')
                      ->orWhere('mobile', 'like', '%' . $search_value . '%');
                 });
            }
        }

        // Date Range Filter
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Status Filter
        if ($request->has('status') && is_array($request->status)) {
            $query->whereIn('status', $request->status);
        }

        $users = $query->orderBy('id', 'desc')->paginate(10);
        
        return view('admin.users.users')->with(compact('users'));
    }



    // AJAX를 사용한 회원 상태(활성/비활성) 업데이트
    public function updateUserStatus(Request $request) {
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기

            if ($data['status'] == 'Active' || $data['status'] == '활성') { // 상태값에 따라 0 또는 1로 전환
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

    // 회원 추가 및 수정
public function addEditUser(Request $request, $id = null)
{
    Session::put('page', 'add_edit_user');
    
    $vendorDetails = null;
    $businessDetails = null;
    $bankDetails = null;

    if ($id == "") {
        $title = "회원 등록";
        $user = new User;
        $message = "회원이 성공적으로 등록되었습니다!";
    } else {
        $title = "회원 수정";
        $user = User::find($id);
        if (!$user) {
            return redirect()->back()->with('error_message', '회원을 찾을 수 없습니다.');
        }
        $message = "회원 정보가 성공적으로 수정되었습니다!";

        // Fetch vendor-related details if exists
        if ($user->vendor_id) {
            $vendorDetails = Vendor::find($user->vendor_id);
            $businessDetails = VendorsBusinessDetail::where('vendor_id', $user->vendor_id)->first();
            $bankDetails = VendorsBankDetail::where('vendor_id', $user->vendor_id)->first();
        }
    }

    if ($request->isMethod('post')) {
        $data = $request->all();

        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'username' => 'required|unique:users,username,' . $id,
            'mobile_2' => 'required|numeric',
            'mobile_3' => 'required|numeric',
        ];
        
        // Password validation for new users
        if ($id == "") {
            $rules['password'] = 'required|min:6|confirmed';
        } else {
            if (!empty($data['password'])) {
                $rules['password'] = 'min:6|confirmed';
            }
        }

        $this->validate($request, $rules);

        // Basic Info
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->username = $data['username'];
        $user->gender = $data['gender'] ?? null;
        $user->mobile = $data['mobile_1'] . '-' . $data['mobile_2'] . '-' . $data['mobile_3'];
        
        // Address
        $user->pincode = $data['zipcode'] ?? null;
        $user->address = $data['address1'] ?? null;
        $user->city = $data['address2'] ?? null; // Detailed address
        
        if (!empty($data['password'])) {
             $user->password = bcrypt($data['password']);
        }
        
        $user->type = $data['type'] ?? 'general';
        $user->status = $data['status'] ?? 1;
        
        // Handle Company/Vendor details (Step 2 & 3)
        if ($user->type == 'company' || $user->type == 'vendor') {
            \Illuminate\Support\Facades\DB::beginTransaction();
            try {
                // First save user to get ID if new
                $user->save();

                // 1. Ensure Vendor record exists (or update)
                if (!$user->vendor_id) {
                    $vendor = new \App\Models\Vendor;
                    $vendor->name = $user->name;
                    $vendor->mobile = $user->mobile;
                    $vendor->email = $user->email;
                    $vendor->status = $data['seller_status'] ?? 0;
                    $vendor->save();
                    $user->vendor_id = $vendor->id;
                    $user->save();
                } else {
                    \App\Models\Vendor::where('id', $user->vendor_id)->update([
                        'status' => $data['seller_status'] ?? 0,
                        'name' => $user->name,
                        'mobile' => $user->mobile
                    ]);
                }
                $vendor_id = $user->vendor_id;

                // 2. Business Details
                $license_number = "";
                if (!empty($data['business_license_1']) && !empty($data['business_license_2']) && !empty($data['business_license_3'])) {
                    $license_number = $data['business_license_1'] . '-' . $data['business_license_2'] . '-' . $data['business_license_3'];
                }

                $businessData = [
                    'shop_name' => $data['shop_name'] ?? '',
                    'shop_business_type' => $data['shop_business_type'] ?? '',
                    'business_license_number' => $license_number,
                    'shop_mobile' => $data['shop_mobile'] ?? '',
                    'shop_pincode' => $data['shop_zipcode'] ?? '',
                    'shop_address' => $data['shop_address1'] ?? '',
                    'shop_address_detail' => $data['shop_address2'] ?? '',
                ];

                if ($request->hasFile('address_proof_image')) {
                    $img = $request->file('address_proof_image');
                    if ($img->isValid()) {
                        $imgName = 'license_' . rand(111, 99999) . '.' . $img->getClientOriginalExtension();
                        $img->move('front/images/bank_copies/', $imgName);
                        $businessData['address_proof_image'] = $imgName;
                    }
                }
                \App\Models\VendorsBusinessDetail::updateOrCreate(['vendor_id' => $vendor_id], $businessData);

                // 3. Bank Details
                $bankData = [
                    'bank_name' => $data['bank_name'] ?? '',
                    'account_number' => $data['account_number'] ?? '',
                    'account_holder_name' => $data['account_holder_name'] ?? '',
                ];

                if ($request->hasFile('bank_copy_image')) {
                    $img = $request->file('bank_copy_image');
                    if ($img->isValid()) {
                        $imgName = 'bankbook_' . rand(111, 99999) . '.' . $img->getClientOriginalExtension();
                        $img->move('front/images/bank_copies/', $imgName);
                        $bankData['bank_copy_image'] = $imgName;
                    }
                }
                \App\Models\VendorsBankDetail::updateOrCreate(['vendor_id' => $vendor_id], $bankData);

                \Illuminate\Support\Facades\DB::commit();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollback();
                return redirect()->back()->with('error_message', '회원사 정보 처리 중 오류 발생: ' . $e->getMessage());
            }
        } else {
            $user->save();
        }

        return redirect('admin/users')->with('success_message', $message);
    }

    return view('admin.users.add_edit_user')->with(compact('title', 'user', 'vendorDetails', 'businessDetails', 'bankDetails'));
}    

    // 회원 삭제
    public function deleteUser($id)
    {
        User::where('id', $id)->delete();
        $message = "User has been deleted successfully!";
        return redirect()->back()->with('success_message', $message);
    }
}