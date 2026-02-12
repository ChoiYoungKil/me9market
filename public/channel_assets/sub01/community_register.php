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
                                <li><a href="../sub01/shop_info.php"><span>Shop채널 정보</span></a></li>
                                <li><a href="../sub01/shop_product01.php"><span>판매상품</span></a></li>
                                <li><a href="#" class="on"><span>커뮤니티</span></a></li>
                            </ul>
                        </div>
                        <div class="conbx">
                            <div class="con_w">
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="160px">
                                            <col width="">
                                            <col width="160px">
                                            <col width="">
                                        </colgroup>
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>등록일</span></th>
                                                <td>0000.00.00</td>
                                                <th class="w160"><span>작성자</span></th>
                                                <td>
                                                    <input type="text" value="" required="required" placeholder="Shop 채널명">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>분류</span></th>
                                                <td colspan="3">
                                                    <ul class="chk01">
                                                        <li>
                                                            <input type="radio" name="radio1" id="radio1_1" checked="">
                                                            <label for="radio1_1">공지</label>
                                                        </li>
                                                        <li>
                                                            <input type="radio" name="radio1" id="radio1_2">
                                                            <label for="radio1_2">일반</label>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>제목</span></th>
                                                <td colspan="3">
                                                    <input type="text" value="" required="required" placeholder="게시판 제목입니다">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>내용</span></th>
                                                <td colspan="3">
                                                    <textarea class="h2" value="" required="required" placeholder="게시판 내용입니다"></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>첨부파일</span></th>
                                                <td colspan="3">
                                                    <div class="fileBox">
                                                        <input type="text" class="fileName" readonly="readonly">
                                                        <label for="uploadBtn" class="btn_file">찾아보기</label>
                                                        <input type="file" id="uploadBtn" class="uploadBtn" name="bbs_file1">
                                                        <div class="del_btn">삭제</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            
                                <div class="btm_btn mt10">
                                    <a href="../sub01/shop_community.php" class="col5">목록</a>
                                    <a href="#">등록하기</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            /* 파일 */
            var uploadFile = $('.fileBox .uploadBtn');
            uploadFile.on('change', function(){
                if(window.FileReader){
                    var filename = $(this)[0].files[0].name;
                } else {
                    var filename = $(this).val().split('/').pop().split('\\').pop();
                }
                $(this).parents('.fileBox').find('.fileName').val(filename);
                $(this).parents('.fileBox').find('.fileName').addClass("on");
            });
        </script>
<?php include __DIR__ ."/../inc/footer.php"; ?>