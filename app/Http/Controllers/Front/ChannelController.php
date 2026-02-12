<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChannelController extends Controller
{


    public function index()
    {
        // 판매자 제한 확인
        if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
            $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if ($user->type == 'vendor' && $user->status == 0) {
                return redirect()->route('channel.complete_profile');
            }
        }
        return view('channel.index', ['dep1_id' => '00']);
    }

    public function login()
    {
        return view('channel.login');
    }

    // 상점 관리 (Sub01)
    public function shopList()
    {
        return view('channel.sub01.shop_list', ['dep1_id' => '01']);
    }

    public function shopRegister()
    {
        return view('channel.sub01.shop_register', ['dep1_id' => '01']);
    }

    public function shopInfo()
    {
        return view('channel.sub01.shop_info', ['dep1_id' => '01']);
    }

    public function shopProduct01()
    {
        return view('channel.sub01.shop_product01', ['dep1_id' => '01']);
    }

    public function shopProduct02()
    {
        return view('channel.sub01.shop_product02', ['dep1_id' => '01']);
    }

    public function shopCommunity()
    {
        return view('channel.sub01.shop_community', ['dep1_id' => '01']);
    }

    public function productOwn()
    {
        return view('channel.sub02.product_own', ['dep1_id' => '02']);
    }

    public function productPublic()
    {
        return view('channel.sub02.product_public', ['dep1_id' => '02']);
    }

    public function productPartial()
    {
        return view('channel.sub02.product_partial', ['dep1_id' => '02']);
    }

    public function productRequest()
    {
        return view('channel.sub02.product_request', ['dep1_id' => '02']);
    }
    
    public function community()
    {
        return view('channel.sub01.shop_community', ['dep1_id' => '01']);
    }

    public function communityRegister()
    {
        return view('channel.sub01.community_register', ['dep1_id' => '01']);
    }

    public function communityView()
    {
        return view('channel.sub01.community_view', ['dep1_id' => '01']);
    }

    public function communityUpdate()
    {
        return view('channel.sub01.community_update', ['dep1_id' => '01']);
    }

    public function infoUpdate()
    {
        return view('channel.sub01.info_update', ['dep1_id' => '01']);
    }

    // 상품 관리 (Sub02) 관련 메서드는 위에 이미 정의되어 있습니다.

    // 주문 관리 (Sub04)
    public function orderList()
    {
        return view('channel.sub04.order_list', ['dep1_id' => '04']);
    }
    
    public function orderCancelList()
    {
        return view('channel.sub04.order_cancel_list', ['dep1_id' => '04']);
    }

    public function orderReturnRequestList()
    {
        return view('channel.sub04.order_return_request_list', ['dep1_id' => '04']);
    }

    public function orderExchangeRequestList()
    {
        return view('channel.sub04.order_exchange_request_list', ['dep1_id' => '04']);
    }
    
    public function orderInfo()
    {
        return view('channel.sub04.inc.pop_order_info'); // 독립형 또는 팝업으로 사용될 수 있습니다.
    }
    
    // 추가 정보 / 설정 (Sub00)
    public function deliveryChargeList()
    {
        return view('channel.sub00.delivery_charge_list', ['dep1_id' => '00']);
    }

    public function cancelRefundList()
    {
        return view('channel.sub00.cancel_refund_list', ['dep1_id' => '00']);
    }

    public function infoManagement()
    {
        return view('channel.sub00.info_management', ['dep1_id' => '00']);
    }

    public function orderManagerList()
    {
        return view('channel.sub00.order_manager_list', ['dep1_id' => '00']);
    }

    public function pointList()
    {
        return view('channel.sub00.point_list', ['dep1_id' => '00']);
    }
    
    public function subList()
    {
        return view('channel.sub00.sub_accounts_list', ['dep1_id' => '00']);
    }

    // 채널 등록 (판매자)
    public function register()
    {
        return view('channel.register');
    }

    public function registerSubmit(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            // 유효성 검사
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'name'     => 'required|string|max:100',
                'mobile'   => 'required|numeric|digits_between:10,15|unique:admins|unique:vendors',
                'email'    => 'required|email|max:150|unique:admins|unique:vendors',
                'password' => 'required|min:6',
                'shop_name' => 'required|string|max:150',
                'shop_mobile' => 'required|numeric|digits_between:10,15',
                'business_license_number' => 'required|string|max:150',
                'shop_business_type' => 'required|in:1,2,3', // 1: 개인, 2: 개인 사업자, 3: 법인 사업자
                'shop_pincode' => 'nullable|string|max:20',
                'shop_address' => 'nullable|string|max:255',
                'shop_address_detail' => 'nullable|string|max:255',
                'bank_name' => 'required|string|max:100',
                'bank_account_number' => 'required|string|max:100',
                'bank_account_holder_name' => 'required|string|max:100',
                'bank_copy_image' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
                'accept'   => 'required'
            ], [
                'accept.required' => '이용약관 및 개인정보 처리방침에 동의해 주세요.',
                'email.unique'    => '이미 존재하는 이메일입니다.',
                'mobile.unique'   => '이미 존재하는 휴대폰 번호입니다.',
                'shop_business_type.required' => '사업자 유형을 선택해 주세요.'
            ]);

            if ($validator->passes()) {
                \Illuminate\Support\Facades\DB::beginTransaction();

                try {
                    // 1. 판매자 생성
                    $vendor = new \App\Models\Vendor;
                    $vendor->name   = $data['name'];
                    $vendor->mobile = $data['mobile'];
                    $vendor->email  = $data['email'];
                    $vendor->status = 0; // 관리자 승인 전까지 비활성 상태
                    date_default_timezone_set('Asia/Seoul'); 
                    $vendor->created_at = date('Y-m-d H:i:s');
                    $vendor->updated_at = date('Y-m-d H:i:s');
                    $vendor->save();

                    $vendor_id = \Illuminate\Support\Facades\DB::getPdo()->lastInsertId();

                    // 파일 업로드
                    $bank_copy_path = null;
                    if ($request->hasFile('bank_copy_image')) {
                        $image_tmp = $request->file('bank_copy_image');
                        if ($image_tmp->isValid()) {
                            $extension = $image_tmp->getClientOriginalExtension();
                            $fileName = 'bank_copy_' . time() . '.' . $extension;
                            $destinationPath = public_path('images/proofs/bank');
                            if (!file_exists($destinationPath)) {
                                mkdir($destinationPath, 0777, true);
                            }
                            $image_tmp->move($destinationPath, $fileName);
                            $bank_copy_path = 'images/proofs/bank/' . $fileName;
                        }
                    }

                    // 2. 판매자 사업자 상세 정보 생성
                    \Illuminate\Support\Facades\DB::table('vendors_business_details')->insert([
                        'vendor_id' => $vendor_id,
                        'shop_name' => $data['shop_name'],
                        'shop_mobile' => $data['shop_mobile'],
                        'business_license_number' => $data['business_license_number'],
                        'shop_business_type' => $data['shop_business_type'] ?? null,
                        'shop_address' => $data['shop_address'] ?? '',
                        'shop_address_detail' => $data['shop_address_detail'] ?? '',
                        'shop_city' => '', // 주소에서 추출 가능하지만 현재는 수동 입력
                        'shop_state' => '',
                        'shop_country' => '',
                        'shop_pincode' => $data['shop_pincode'] ?? '',
                        'bank_name' => $data['bank_name'] ?? '',
                        'bank_account_number' => $data['bank_account_number'] ?? '',
                        'bank_account_holder_name' => $data['bank_account_holder_name'] ?? '',
                        'bank_copy_image' => $bank_copy_path,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    // 3. Admin 생성 (유형: Vendor)
                    $admin = new \App\Models\Admin;
                    $admin->type      = 'vendor';
                    $admin->vendor_id = $vendor_id;
                    $admin->name      = $data['name'];
                    $admin->mobile    = $data['mobile'];
                    $admin->email     = $data['email'];
                    $admin->password  = bcrypt($data['password']);
                    $admin->status    = 0; // 비활성 상태
                    $admin->created_at = date('Y-m-d H:i:s');
                    $admin->updated_at = date('Y-m-d H:i:s');
                    $admin->save();

                    // 4. 확인 이메일 발송
                    $email = $data['email'];
                    $messageData = [
                        'email' => $data['email'],
                        'name'  => $data['name'],
                        'code'  => base64_encode($data['email'])
                    ];

                    \Illuminate\Support\Facades\Mail::send('emails.vendor_confirmation', $messageData, function ($message) use ($email) {
                        $message->to($email)->subject('판매자 계정 확인');
                    });

                    \Illuminate\Support\Facades\DB::commit();

                    // 성공 응답 반환
                    $redirectTo = route('channel.login'); 
                    return response()->json([
                        'type'    => 'success',
                        'url'     => $redirectTo,
                        'message' => '판매자 등록 신청이 완료되었습니다. 이메일을 확인하여 계정 확인을 완료해 주세요.'
                    ]);

                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\DB::rollback();
                    return response()->json([
                        'type'    => 'error',
                        'errors'  => ['email' => [$e->getMessage()]]
                    ]);
                }

            } else {
                return response()->json([
                    'type'   => 'error',
                    'errors' => $validator->messages()
                ]);
            }
        }
    }
    // 프로필 완성 (상태가 0인 판매자용)
    public function completeProfile()
    {
        // 로그인 확인
        if (!\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
            return redirect()->route('channel.login');
        }

        $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if ($user->type != 'vendor') {
            return redirect()->route('admin.dashboard');
        }

        // 이미 제출되었는지 확인 (간단한 확인: 상세 정보에 유효한 사업자 등록 번호가 있는지 확인)
        $details = \Illuminate\Support\Facades\DB::table('vendors_business_details')
                        ->where('vendor_id', $user->vendor_id)->first();

        // 상세 정보가 존재하고 사업자 번호가 있으면 '심사 중' 페이지 표시
        if ($details && !empty($details->business_license_number)) {
             return view('channel.application_submitted');
        }

        return view('channel.complete_profile');
    }

    public function completeProfileSubmit(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            // 유효성 검사 (입력 양식과 비슷하지만 사업자 상세 정보에 대해 엄격함)
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'shop_name' => 'required|string|max:150',
                'shop_business_type' => 'required|in:1,2,3',
                'business_license_number' => 'required|string|max:150',
                'shop_mobile' => 'required|numeric|digits_between:10,15',
                'bank_name' => 'required|string|max:100',
                'bank_account_number' => 'required|string|max:100',
                'bank_account_holder_name' => 'required|string|max:100',
                'bank_copy_image' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
                'accept'   => 'required'
            ], [
                'accept.required' => '이용약관 및 개인정보 처리방침에 동의해 주세요.',
                'shop_business_type.required' => '사업자 유형을 선택해 주세요.'
            ]);

            if ($validator->passes()) {
                $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
                $vendor_id = $user->vendor_id;

                try {
                    // 파일 업로드
                    $bank_copy_path = null;
                    if ($request->hasFile('bank_copy_image')) {
                        $image_tmp = $request->file('bank_copy_image');
                        if ($image_tmp->isValid()) {
                            $extension = $image_tmp->getClientOriginalExtension();
                            $fileName = 'bank_copy_' . time() . '.' . $extension;
                            $destinationPath = public_path('images/proofs/bank');
                            if (!file_exists($destinationPath)) {
                                mkdir($destinationPath, 0777, true);
                            }
                            $image_tmp->move($destinationPath, $fileName);
                            $bank_copy_path = 'images/proofs/bank/' . $fileName;
                        }
                    }

                    // 사업자 상세 정보 업데이트 또는 삽입
                    \Illuminate\Support\Facades\DB::table('vendors_business_details')->updateOrInsert(
                        ['vendor_id' => $vendor_id],
                        [
                            'shop_name' => $data['shop_name'],
                            'shop_mobile' => $data['shop_mobile'],
                            'business_license_number' => $data['business_license_number'],
                            'shop_business_type' => $data['shop_business_type'],
                            'shop_address' => $data['shop_address'] ?? '',
                            'shop_address_detail' => $data['shop_address_detail'] ?? '',
                            'shop_pincode' => $data['shop_pincode'] ?? '',
                            'bank_name' => $data['bank_name'],
                            'bank_account_number' => $data['bank_account_number'],
                            'bank_account_holder_name' => $data['bank_account_holder_name'],
                            // 새로운 이미지가 제공된 경우에만 이미지 업데이트
                            // For simplicty in updateOrInsert, if $bank_copy_path is null, we might lose old one if we validly overwrite?
                            // Actually updateOrInsert merges. But if we pass null, it sets null?
                            // Let's check first.
                            'updated_at' => date('Y-m-d H:i:s')
                        ]
                    );

                    // 이미지가 제공된 경우, 기존 이미지가 null로 덮어씌워지지 않도록 명시적으로 업데이트
                    if ($bank_copy_path) {
                        \Illuminate\Support\Facades\DB::table('vendors_business_details')
                            ->where('vendor_id', $vendor_id)
                            ->update(['bank_copy_image' => $bank_copy_path]);
                    } elseif (\Illuminate\Support\Facades\DB::table('vendors_business_details')->where('vendor_id', $vendor_id)->doesntExist()) {
                         // 새로 삽입하고 이미지가 없으면 null입니다.
                    }

                    // 삽입된 경우 created_at이 설정되었는지 확인
                    // (updateOrInsert doesn't automatically handle created_at on insert if not in values)
                    // We can just rely on updated_at for now.

                    return response()->json([
                        'type'    => 'success',
                        'url'     => route('channel.complete_profile'), // 심사 중 페이지를 보여주기 위해 새로고침
                        'message' => '신청서가 성공적으로 제출되었습니다!'
                    ]);

                } catch (\Exception $e) {
                    return response()->json([
                        'type'    => 'error',
                        'errors'  => ['error' => [$e->getMessage()]]
                    ]);
                }
            } else {
                 return response()->json([
                    'type'   => 'error',
                    'errors' => $validator->messages()
                ]);
            }
        }
    }
}
