@php($privateAccessRowsValue = (string) ($privateAccessRowsValue ?? ''))

<div class="popup_bx" data-id="pop_private_access_manager">
    <div class="pop_w">
        <div class="pop_inner">
            <div class="pop_con w800">
                <div class="close_btn close1">닫기</div>
                <div class="page_info type2">
                    <div class="ttl">비공개 접속가능 인원관리</div>
                </div>

                <div class="conbx">
                    <div class="con_w">
                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="175px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>기본양식</span></th>
                                        <td>
                                            <a href="{{ route('channel.shop.private_access.template') }}" class="btn02 col5">기본양식 다운로드</a>
                                            <div class="col2 fs2 mt5">양식 컬럼: 휴대폰번호, 입장코드, 회원ID(선택)</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>엑셀 업로드</span></th>
                                        <td>
                                            <input type="file" name="private_access_file" accept=".xlsx,.xls,.csv,.txt">
                                            <div class="col2 fs2 mt5">업로드한 파일은 아래 직접 입력 목록과 합쳐서 저장됩니다.</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="w160"><span>직접 입력</span></th>
                                        <td>
                                            <textarea name="private_access_rows" id="private_access_rows" class="wFull" rows="10" placeholder="휴대폰번호,입장코드&#10;010-1234-5678,ABCD1234">{{ $privateAccessRowsValue }}</textarea>
                                            <div class="col2 fs2 mt5">한 줄에 한 명씩 입력합니다. 입장코드를 생략하면 비공개 비밀번호를 사용합니다.</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="btm_btn mt10">
                    <a href="{{ url()->current() }}" class="col5 close_btn private_access_apply">적용</a>
                </div>
            </div>
        </div>
    </div>
</div>
