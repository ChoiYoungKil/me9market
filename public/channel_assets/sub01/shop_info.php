<?php include __DIR__ ."/../inc/doctype.php"; ?>
<?php
	$page_type = "sub";
	$dep1_id = "01";
	$dep1_tit = "Shop채널관리";
?>
<?php include __DIR__ ."/../inc/header.php"; ?>
		<div id="container_w">
			<div id="contents">
                <div class="row">
                    <div class="box box1">
                        <div class="page_info">
                            <div class="ttl">Shop채널 상세페이지</div>
                            <ul class="dep">
                                <li>HOME</li>
                                <li>Shop채널관리</li>
                                <li>Shop채널 상세페이지</li>
                            </ul>
                        </div>
                        <div class="tab_bx1">
                            <ul>
                                <li><a href="#" class="on"><span>Shop채널 정보</span></a></li>
                                <li><a href="../sub01/shop_product01.php"><span>판매상품</span></a></li>
                                <li><a href="../sub01/shop_community.php"><span>커뮤니티</span></a></li>
                            </ul>
                        </div>
                        <div class="conbx">
                            <div class="con_w">
                                <div class="ttl01">Shop 채널 기본정보</div>
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>Shop 채널코드</span></th>
                                                <td>Me9-2024-099833</td>
                                                <th class="w160"><span>등록일</span></th>
                                                <td>2024-10-20 00:00</td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>채널상태</span></th>
                                                <td>중지</td>
                                                <th class="w160"><span>공개여부</span></th>
                                                <td>공개</td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>채널명</span></th>
                                                <td colspan="3">Shop 채널명</td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>구매권한</span></th>
                                                <td>비회원 구매가능</td>
                                                <th class="w160"><span>채널사용주기</span></th>
                                                <td>기간제  /  2024-01-00  00시 ~ 2024-10-00  00시</td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>카피라이트</span></th>
                                                <td colspan="3">© 2024. 회사명 Inc. All rights reserved.</td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>그룹 keyword</span></th>
                                                <td colspan="3">
                                                    <ul class="tag_list">
                                                        <li>그룹명 #2</li>
                                                        <li>그룹명 키워드 #1</li>
                                                        <li>그룹명 키워드 #2</li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="con_w">
                                <div class="ttl01">Shop 채널 (모니터링) 관리자 정보</div>
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="">
                                            <col width="175px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>Shop 채널 관리자 여부</span></th>
                                                <td>사용</td>
                                                <th class="w160"><span>Shop 채널 관리자 성명</span></th>
                                                <td>홍길동</td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>로그인 ID</span></th>
                                                <td>shopdmin</td>
                                                <th class="w160"><span>로그인 PW</span></th>
                                                <td>1234$$</td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>정산방법</span></th>
                                                <td>판매 개당 비용</td>
                                                <th class="w160"><span>정산요율</span></th>
                                                <td>판매개당 / 1000 원</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="con_w">
                                <div class="ttl01">OG TAG</div>
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="100px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>OG 사용여부</span></th>
                                                <td colspan="2">사용</td>
                                            </tr>
                                            <tr>
                                                <th class="w160" rowspan="3"><span>OG TAG</span></th>
                                                <td>Title</td>
                                                <td>사이트 제목</td>
                                            </tr>
                                            <tr>
                                                <td>Description</td>
                                                <td>사이트 내용</td>
                                            </tr>
                                            <tr>
                                                <td>Image</td>
                                                <td>
                                                    <img src="../images/sub/thum01.jpg">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="con_w">
                                <div class="ttl01">이미지</div>
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="175px">
                                            <col width="100px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>로고 이미지</span></th>
                                                <td colspan="2"><img src="../images/sub/thum01.jpg"></td>
                                            </tr>
                                            <tr>
                                                <th class="w160" rowspan="5"><span>배너 이미지</span></th>
                                                <td>#1</td>
                                                <td><img src="../images/sub/thum01.jpg"></td>
                                            </tr>
                                            <tr>
                                                <td>#2</td>
                                                <td><img src="../images/sub/thum01.jpg"></td>
                                            </tr>
                                            <tr>
                                                <td>#3</td>
                                                <td><img src="../images/sub/thum01.jpg"></td>
                                            </tr>
                                            <tr>
                                                <td>#4</td>
                                                <td><img src="../images/sub/thum01.jpg"></td>
                                            </tr>
                                            <tr>
                                                <td>#5</td>
                                                <td><img src="../images/sub/thum01.jpg"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                                
                    <div class="btm_btn">
                        <a href="../sub01/info_update.php" class="col2">정보수정</a>
                        <a href="#">Shop채널보기</a>
                        <a href="../sub01/shop_list.php" class="col5">목록</a>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
           
        </script>
<?php include __DIR__ ."/../inc/footer.php"; ?>