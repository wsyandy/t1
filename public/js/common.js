;
$(function () {

    $(".batch_select").click(function (event) {
        event.preventDefault();
        event.stopPropagation();
        var target = $(this).data("target");
        var option = $(this).data("select_option");
        //复选框列表为空时刷新页面
        if ($("#" + target + " input:checkbox").length <= 0) {
            top.window.location.reload();
        }
        if (option == "all") {
            $("#" + target + " input:checkbox").each(function () {
                $(this).prop("checked", true);
            });
        }
        if (option == "reverse") {
            $("#" + target + " input:checkbox").each(function () {
                if ($(this).is(":checked")) {
                    $(this).prop("checked", false);
                } else {
                    $(this).prop("checked", true);
                }
            });
        }
    });
    $(".selected_action").click(function (event) {
        event.preventDefault();
        event.stopPropagation();

        var target = $(this).data("target");
        var value = $(this).data("action");
        var form_id = $(this).data("formid");
        $("#" + target).val(value);
        var form = $("#" + form_id);
        var c = $(this);
        $(this).attr({"disabled": "disabled"});
        form.ajaxSubmit({
            success: function () {
                form.find("input:checked").parents(".object_unit").remove();
                c.removeAttr("disabled");
                //复选框列表为空时刷新页面
                if ($("#batch_form input:checkbox").length <= 0) {
                    top.window.location.reload();
                }
            }
        });
    });
    $(".batch_pass").click(function (event) {
        event.preventDefault();
        event.stopPropagation();
        var ids = "";
        var target = $(this).data("target");
        $("#" + target + " input:checkbox").each(function () {
            ids = ids + "," + $(this).val();
        });
        var token = $("meta[name='csrf-token']").attr("content");
        var form = $("#" + target).parent("form");
        console.log(form);
        var action = $(this).data("action");

        /*$(".object_unit").remove();*/
        var c = $(this);
        $(this).attr({"disabled": "disabled"});
        $.ajax({
            url: action,
            type: 'post',
            data: {'ids': ids, 'authenticity_token': token},
            success: function (data) {
                $(".object_unit").remove();
                c.removeAttr("disabled");
            }
        });
    });

    $('.user_once_click').click(function (event) {
        event.preventDefault();
        var self = $(this);
        var url = self.attr("href");
        var auth = self.data('auth');
        var user_id = self.data('target');
        $.get(url, function (resp) {
            if (resp.redirect_url) {
                top.window.location.href = resp.redirect_url;
                return;
            }
            if (auth == 1 || auth == 2) {
                self.parents().find('dd' + user_id).remove();
            }
            //复选框列表为空时刷新页面
            if ($("#batch_form input:checkbox").length <= 0) {
                top.window.location.reload();
            }
        });
        return false;
    });

    $(".album_once_click").click(function (event) {
        event.preventDefault();
        var self = $(this);

        var url = $(this).attr("href");
        $.get(url, function (resp) {
            self.parents(".unit,.object_unit").remove();
        })
        return false;
    })
});
