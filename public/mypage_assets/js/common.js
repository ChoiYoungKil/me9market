$(function(){
    //header
    /*$(window).resize(function(){
        if($(window).width() > 1024){
            $("#header .r_menu").css("height", ($("#container_w").height() + 20)+"px");
        }
    });
    if($(window).width() > 1024){
        $("#header .r_menu").css("height", ($("#container_w").height() + 20)+"px");
    }*/
    $("#header .t_menu .r_menu_btn").click(function(){
        $(this).toggleClass("on");
        if($(window).width() > 1024) {
            $("#header .r_menu").stop().slideToggle(300);
        }else {
            $("#header .r_menu").stop().fadeToggle(300);
        }
    });
    $("#header .l_menu_btn").click(function(){
        $(this).toggleClass("on");
        $("#l_menu").stop().fadeToggle(300);
        setTimeout(function(){
            $("#l_menu").scrollTop(0);
        },250);
    });
    
    $("#l_menu .con2 .con_w .dep1.on .dep2_wrap").show();
    $("#l_menu .con2 .con_w .dep1.arrow > a").click(function(){
        $("#l_menu .con2 .con_w .dep1.arrow").removeClass("on");
        $("#l_menu .con2 .con_w .dep1.arrow .dep2_wrap").stop().slideUp(300);
        $(this).parents(".arrow").addClass("on");
        $(this).siblings(".dep2_wrap").stop().slideDown(300);
        
        return false;
    });
    
	//top_btn
    $('#footer .top_btn').click(function(){
        $('body,html').animate({'scrollTop':0});
    });
    
    $(".h_full").css("height", $(window).height());
    $(window).resize(function(){
        $(".h_full").css("height", $(window).height());
    });
})