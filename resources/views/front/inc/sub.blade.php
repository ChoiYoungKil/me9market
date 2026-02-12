<div id="s_visual">
    <div class="ttl_bx">
        <div class="txt1">{{ $dep1_tit ?? '' }}</div>
        <div class="txt2">{{ $dep2_sub ?? '' }}</div>
    </div>
    @if(isset($dep2_id))
        <div class="tab_box">
            <div class="on_txt m_show"><span>{{ $dep2_tit ?? '' }}</span></div>
            @include("front.inc.snb" . $dep1_id)
        </div>
    @endif
</div>
<script type="text/javascript">
    $("#header .menu_bx .dep{{ $dep1_id ?? '' }}").addClass("on");

    @if(isset($dep2_id))
        $("#header .menu_bx .dep{{ $dep1_id ?? '' }} .dep2_wrap > li:nth-child({{ $dep2_id }}) a").addClass("colOn");
        setTimeout(function () {
            $("#s_visual .tab_box .dep2_wrap > li:nth-child({{ $dep2_id }})").addClass("on");
        }, 10);
    @endif

    $("#s_visual .tab_box .on_txt").click(function () {
        $(this).toggleClass("on");
        $("#s_visual .tab_box .dep2_wrap").stop().slideToggle(300);
    });
</script>