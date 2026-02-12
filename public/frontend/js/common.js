$(function(){
	$("#header .menu_btn").click(function(){
        $(this).toggleClass("on");
        $("#header .menu_bx").stop().fadeToggle(300);
    });
    
    //top_btn
    $('#footer .top_btn').click(function(){
        $('body,html').animate({'scrollTop':0});
    });
})