$(function(){
	$("#header .r_menu_btn").click(function(){
        $(this).toggleClass("on");
        $("#header .r_menu .menu_w").stop().slideToggle(300);
    });
})