<script type="text/javascript">
    $(function () {
        $("input[name='use_purchase_sms']").on("change", function () {
            if ($("#purchase_sms_on").is(":checked")) {
                $(".purchase_sms_row").stop().fadeIn(200);
            } else {
                $(".purchase_sms_row").stop().fadeOut(200);
            }
        });
    });
</script>
