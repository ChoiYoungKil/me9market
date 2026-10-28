<?php

namespace App\Services;

use App\Models\Notice;
use App\Models\Order;
use App\Models\User;

class StoryboardReview
{
    public const REVIEWED_AT = '2026-10-28';

    /** Entry links never create records, submit claims, or switch the signed-in account. */
    public function pages(?User $member): array
    {
        $definitions = [
            1=>['표지','/','PPT 문서 표지'],
            2=>['개정이력','/','PPT 문서 개정이력'],
            3=>['개정이력 상세','/','PPT 문서 개정이력'],
            4=>['개정이력 상세','/','PPT 문서 개정이력'],
            5=>['홈페이지 영역 구분','/','홈페이지 영역 구분 페이지'],
            6=>['메인 영역 구분','/','메인 화면 영역 구분 페이지'],
            7=>['메인','/','화면 스크롤과 메뉴 이동, 메인 콘텐츠 확인'],
            8=>['서비스안내 영역 구분','/service','서비스안내 영역 구분 페이지'],
            9=>['서비스안내','/service','메인과 같은 디자인, 스크롤과 메뉴 이동 확인'],
            10=>['주요기능 영역 구분','/features','주요기능 영역 구분 페이지'],
            11=>['주요기능','/features','주요기능 콘텐츠와 메뉴 이동 확인'],
            12=>['가입안내 영역 구분','/subscription-information','가입안내 영역 구분 페이지'],
            13=>['가입안내','/subscription-information','회원·회원사·판매자 가입 단계 안내 확인'],
            14=>['고객센터 영역 구분','/notice','고객센터 영역 구분 페이지'],
            15=>['공지사항 목록','/notice','등록된 제목·공지 표시·검색 확인. 관리자에서 등록·수정·삭제 확인'],
            16=>['공지사항 상세','/notice','목록에서 제목 선택 후 본문·첨부파일 확인. 공개된 공지가 있으면 상세로 바로 이동'],
            17=>['자주묻는질문','/faq','분류·검색·질문 펼치기. 관리자 등록·수정·삭제 확인'],
            18=>['제휴/문의','/contact','회원 로그인 후 검수용 제목·본문으로 문의 등록 확인'],
            19=>['주문조회 영역 구분','/nonmember/order/check','비회원 주문조회 영역 구분 페이지'],
            20=>['비회원 주문조회','/nonmember/order/check','해당 검수 주문의 주문번호·주문자 정보 입력. 잘못된 정보 거부 확인'],
            21=>['비회원 주문 상세','/nonmember/order/check','20번 조회를 먼저 완료한 뒤 상품·결제·배송 정보 확인'],
            22=>['비회원 취소 신청','/nonmember/order/check','조회한 결제완료 주문에서 취소 창닫기·사유 선택·접수·판매자 승인 확인'],
            23=>['비회원 반품 신청','/nonmember/order/check','조회한 주문에서 회수방법·주소·기타 사유·접수·판매자 완료 확인'],
            24=>['비회원 교환 신청','/nonmember/order/check','교환 접수 후 판매자 승인·회수·확정·재발송 확인'],
            25=>['비회원 구매 확정','/nonmember/order/check','구매확정 버튼과 결과 상태 확인'],
            26=>['비회원 판매자 문의','/nonmember/order/check','조회한 상품의 판매자 문의 작성·접수 확인'],
            27=>['회원영역 구분','/member/login','회원영역 구분 페이지'],
            28=>['로그인','/member/login','일반 로그인과 소셜 로그인 확인. 가입 화면은 회원 로그아웃 상태에서 시작'],
            29=>['소셜 회원가입','/member/login','소셜 인증은 본인 계정으로 진행 → 소셜 약관 화면 → 추가정보 단계. 일반 가입과 독립'],
            30=>['일반 회원가입','/register/member','회원 로그아웃 상태에서 아이디·이메일·약관 확인 → 추가정보 단계. 소셜 가입과 독립'],
            31=>['가입 후 추가정보 안내','/register/step1','29번 또는 30번 완료 후 동일한 추가정보 단계로 연결'],
            32=>['Step1 기본정보','/register/step1','기본정보 입력·저장·취소·다음 단계 이동 확인'],
            33=>['Step2 회원사 정보','/register/step2','회원사 정보·검수용 서류·저장·취소 후 Step3 이동 확인'],
            34=>['Step3 판매자 정보','/register/step3','검수용 정산정보·서류 저장·승인 요청·전체관리자 승인 후 인증 상태 확인'],
            35=>['일반회원 아이디 찾기','/find/id','이름·가입 이메일로 아이디 찾기. 불일치 정보 거부 확인'],
            36=>['비밀번호 찾기 / 임시비밀번호 메일','/find/pw','PPT 방식의 임시비밀번호 메일과 로그인 확인. 재발급 시 현재 비밀번호가 바뀌므로 검수 계정으로 직접 진행'],
            37=>['회원 마이페이지','/mypage/main','본인 주문·포인트·문의·방문 채널·승인 상태·바로가기 저장과 반응형 확인'],
            38=>['회원정보 수정','/mypage/profile','생년월일·연락처·선택동의·PG 정보 입력폼과 저장 확인'],
            39=>['비밀번호 변경','/mypage/profile','회원정보의 비밀번호 변경 창 열기. 실제 비밀번호 변경은 사용자 요청으로 보류'],
            40=>['배송지 설정','/mypage/delivery','본인 배송지·기본 표시·등록/수정 버튼 확인'],
            41=>['배송지 등록','/mypage/delivery','배송지 등록 버튼 → 주소·필수값 확인 → 저장·창닫기 확인'],
            42=>['기본 배송지 전환 확인','/mypage/delivery','기본 배송지 전환 → 확인·취소에 따른 저장 상태 확인'],
            43=>['배송지 수정','/mypage/delivery','수정 창에서 변경·저장·닫기, 검수용 추가 배송지 삭제 확인'],
            44=>['회원탈퇴 신청','/mypage/withdraw','폐기 가능한 별도 검수 계정으로 진행. 비밀번호·사유·포인트·타 채널 판매 제한 확인'],
            45=>['회원탈퇴 최종 확인','/mypage/withdraw','별도 검수 계정에서 확인 취소 시 유지, 판매 중이면 거부 확인'],
            46=>['회원탈퇴 완료','/mypage/withdraw','실제 탈퇴 직후 발급된 세션으로만 완료 화면 표시. 로그아웃·재로그인 거부 확인. 현재 주 검수 계정은 탈퇴하지 않음'],
            47=>['방문한 채널','/mypage/visited-channels','같은 판매자의 서로 다른 Shop 방문·검색·이동·개별/전체 삭제 확인'],
            48=>['방문한 채널 QR','/mypage/visited-channels','목록의 QR 버튼 → 확대된 QR·채널명·진입 URL 확인'],
            49=>['포인트 현황','/mypage/points/status','채널·Me9 원장 잔액, 승인된 운영종료 채널의 전환 확인·취소·중복 방지 확인'],
            50=>['포인트 사용 가능 Shop / QR','/mypage/points/status','Shop 채널 보기 → 해당 판매자의 채널·채널별 잔액·QR 확대·바로가기 확인'],
            51=>['포인트 이력','/mypage/points/history','날짜·적립/사용·채널명·코드·금액·상세내용 복합검색, 0건·초기화 확인'],
            52=>['장바구니 목록','/mypage/cart','여러 Shop의 상품·수량·금액·검색·삭제·채널 이동, 재로그인 후 유지 확인'],
            53=>['장바구니 QR','/mypage/cart','상품의 QR 버튼 → 해당 Shop 진입 QR 확대 확인'],
            54=>['찜한 상품','/mypage/wishlist','상품 상세의 찜 등록·해제 → 목록 검색·상품 이동·삭제·재로그인 유지 확인'],
            55=>['회원 주문 목록','/mypage/order/list','본인 주문 상태·날짜 조회, 각 상태에서 가능한 버튼·페이지 이동 확인'],
            56=>['회원 판매자 문의','/mypage/order/list','주문 상품의 판매자 문의하기 → 창닫기·작성·접수. 본인 주문 및 해당 판매자 연결 확인'],
            57=>['회원 취소 신청','/mypage/order/list','결제완료/배송대기 상품 → 취소신청 → 사유·기타내용·창닫기·접수 확인'],
            58=>['회원 반품 신청','/mypage/order/list','반품신청 → 자동/수동 회수·주소·기타 사유·접수 확인'],
            59=>['회원 교환 신청','/mypage/order/list','교환신청 → 자동/수동 회수·창닫기·재작성·접수 확인'],
            60=>['회원 구매 확정 / 반 별점','/mypage/order/list','4.5점 선택·선택 취소·재선택·구매확정 저장 확인. 마우스 이동만으로 반 별점 미리보기는 추가 확인 필요'],
            61=>['회원 취소 목록','/mypage/order/list?tab=cancel','취소요청 → 판매자 승인 → 취소완료·모의 환불 확인'],
            62=>['회원 반품 목록','/mypage/order/list?tab=return','반품요청 → 회수완료 → 반품확정 → 회원 반품완료·모의 환불 확인'],
            63=>['회원 교환 목록','/mypage/order/list?tab=exchange','교환 승인·회수·확정 → 원 주문에 재발송 상품 생성 → 송장 입력·배송중 확인'],
            64=>['회원 주문 상세','/mypage/order/list','본인 주문 상세에서 상품 유형·배송·결제·쿠폰·포인트·클레임 사유·환불·재발송 정보 확인'],
        ];
        $documentPages = [1,2,3,4,5,6,8,10,12,14,19,27];
        $notice = Notice::where('status', 1)->latest('id')->first();
        if ($notice) $definitions[16][1] = '/notice/view/'.$notice->id;
        $order = $member ? Order::where('user_id', $member->id)->latest('id')->first() : null;
        if ($order) $definitions[64][1] = '/mypage/order/view?id='.$order->id;
        $pages = [];
        foreach ($definitions as $number => [$title, $path, $steps]) {
            $status = in_array($number, $documentPages, true) ? 'document' : ($number === 39 ? 'deferred' : ($number === 60 ? 'partial' : 'reviewed'));
            $pages[] = compact('number', 'title', 'path', 'steps', 'status') + [
                'status_label'=>['document'=>'문서/구분', 'deferred'=>'실제 변경 보류', 'partial'=>'추가 확인 있음', 'reviewed'=>'시연 확인 기록'][$status],
                'note'=>in_array($number, [61,62,63], true) ? 'PPT 설명 제목은 찜 목록으로 복사되어 있어 화면 ID와 주문 탭 흐름으로 구분했습니다.' : '',
            ];
        }
        return $pages;
    }
}
