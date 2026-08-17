<script type="text/javascript">
    $(function () {
        function privateAccessCount() {
            var raw = $("#private_access_rows").val() || "";
            return raw.split(/\r\n|\r|\n/).filter(function (line) {
                return $.trim(line) !== "";
            }).length;
        }

        function updatePrivateAccessCount() {
            $("#private_access_count").text(privateAccessCount());
        }

        function togglePrivateAccessState() {
            var isPrivate = $("#radio2_2").is(":checked");
            if (isPrivate) {
                $(".private_access_row").stop().fadeIn(200);
                $("#chk1_1").prop("checked", false).prop("disabled", true);
            } else {
                $(".private_access_row").stop().fadeOut(200);
                $("#chk1_1").prop("disabled", false);
            }
        }

        $(document).on("click", ".private_access_open", function (e) {
            e.preventDefault();
            $(".popup_bx[data-id='pop_private_access_manager']").stop().fadeIn(300).scrollTop(0);
            updatePrivateAccessCount();
        });

        $(document).on("click", ".popup_bx[data-id='pop_private_access_manager'] .close_btn", function (e) {
            e.preventDefault();
            $(this).parents(".popup_bx").stop().fadeOut(300);
            updatePrivateAccessCount();
        });

        $(document).on("input", "#private_access_rows", updatePrivateAccessCount);
        $("input[name='is_public']").on("change", togglePrivateAccessState);

        togglePrivateAccessState();
        updatePrivateAccessCount();
    });
</script>
