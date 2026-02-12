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
                                <div class="list_top1">
                                    <div class="count">총 <strong>00</strong> 건</div>
                                    <div class="searh_bx">
                                        <input type="text" placeholder="제목, 내용검색">
                                        <a href="#" class="btn">검색</a>
                                    </div>
                                </div>
                                
                                <div class="tb01">
                                    <table>
                                        <colgroup>
                                            <col width="80px">
                                            <col width="">
                                            <col width="100px">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th>번호</th>
                                                <th>제목</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody class="textL">
                                            <tr>
                                                <td class="t_c">00</td>
                                                <td class="ovH">
                                                    <a href="community_view.php" class="subject on fcol1">공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다. 공지사항 입니다.</a>
                                                </td>
                                                <td class="t_c">2025-10-01</td>
                                            </tr>
                                            <tr>
                                                <td class="t_c">00</td>
                                                <td class="ovH">
                                                    <a href="community_view.php" class="subject fcol1">일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다.</a>
                                                </td>
                                                <td class="t_c">2025-10-01</td>
                                            </tr>
                                            <tr>
                                                <td class="t_c">00</td>
                                                <td class="ovH">
                                                    <a href="community_view.php" class="subject fcol1">일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다. 일반 게시판 제목입니다.</a>
                                                </td>
                                                <td class="t_c">2025-10-01</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!--<div class="no_data">등록된 데이터가 없습니다.</div>-->
                                
                                <div class="btm_btn right mt10">
                                    <!-- 페이징 -->
                                    <div class="page_bx1">
                                        <a href="#" class="page_first">first</a>
                                        <a href="#" class="page_prev">prev</a>
                                        <a href="#" class="num on">1</a>
                                        <a href="#" class="num">2</a>
                                        <a href="#" class="num">3</a>
                                        <a href="#" class="num">4</a>
                                        <a href="#" class="num">5</a>
                                        <a href="#" class="page_next">next</a>
                                        <a href="#" class="page_last">last</a>
                                    </div>
                                    
                                    <a href="../sub01/community_register.php">글쓰기</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
           
        </script>
<?php include __DIR__ ."/../inc/footer.php"; ?>