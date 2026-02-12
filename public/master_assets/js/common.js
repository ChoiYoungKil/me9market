$(function(){
	$("#l_menu .con1 .con_w .dep1.on .dep2_wrap").show();
    $("#l_menu .con1 .con_w .dep1.arrow > a").click(function(){
        $("#l_menu .con1 .con_w .dep1.arrow").removeClass("on");
        $("#l_menu .con1 .con_w .dep1.arrow .dep2_wrap").stop().slideUp(300);
        $(this).parents(".arrow").addClass("on");
        $(this).siblings(".dep2_wrap").stop().slideDown(300);
        
        return false;
    });
})