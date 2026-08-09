<?php include __DIR__ ."/../inc/doctype.php"; ?>
<?php
	$page_type = "sub";
	$dep1_id = "01";
	$dep1_tit = "로그인";
	$head_none = "h_none";
?>
<?php include __DIR__ ."/../inc/header.php"; ?>
		<div id="container">
			<div id="contents">
                <div id="login">
                	<div class="box box1">
                                <div class="inner_bx">
                                    <img src="../images/common/logo1.png" class="logo">
                                    <form>
                                <div class="f_bx">
                                    <input class="mt0" type="text" placeholder="아이디를 입력 해 주세요">
                                    <input type="password" placeholder="비밀번호를 입력 해 주세요">
                                    <ul class="chk01">
                                        <li>
                                            <input type="checkbox" id="idSave">
                                            <label for="idSave">아이디 저장</label>
                                        </li>
                                    </ul>
                                    <a href="./" class="btn">LOGIN</a>
                                </div>
                                    </form>
                                    <div class="sns_bx">
                                        <div class="txt"><span>간편회원 로그인</span></div>
                                        <ul>
                                            <li class="icon1"><a href="./">kakaotalk</a></li>
                                            <li class="icon2"><a href="./">naver</a></li>
                                            <li class="icon3"><a href="./">gmail</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                </div>
	        </div><!-- //contents -->
        </div><!-- //container -->
        <script type="text/javascript">
            $("#login").css("height", $(window).height());
            $(window).resize(function(){
                $("#login").css("height", $(window).height());
            });
        </script>

<?php include __DIR__ ."/../inc/footer.php"; ?>