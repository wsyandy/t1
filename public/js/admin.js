;
function debug(text) {
    console.log(text);
}
function startWait() {
    stopWait();
    $('body').append("<div id='waiting'>" +
        "<p class='tip' style=''>请稍候....... <a href='#' class='close_btn'>关闭</a></p></div>");
    var screenWidth = $(window).width();//当前窗口宽度
    var screenHeight = $(window).height();//当前窗口高度

    $("#waiting").css({
        "display": "",
        "position": "fixed",
        "background": "#000",
        "z-index": "9999",
        "-moz-opacity": "0.3",
        "opacity": ".30",
        "filter": "alpha(opacity=30)",
        "width": screenWidth,
        "height": screenHeight,
        "top": 0,
        "text-align": "center",
        "color": '#000000',
        "padding-top": screenHeight / 2
    });
    $("#waiting .tip").css({
        "text-align": "center",
        "color": "#ff0000",
        "background-color": "#FFFFFF",
        "width": "200px",
        "margin": "0px auto",
        "height": "100px",
        "padding-top": "40px"
    });
    $("#waiting .close_btn").click(function (event) {
        event.preventDefault();
        stopWait();
    });


}

function stopWait() {
    $("#waiting").remove();
}

$(function () {

    $(".default_table").addClass('table table-striped table-condensed').removeClass('default_table');
    $('.default-btn').addClass('btn btn-default')

    $("form").attr("autocomplete", "off");
    $("body").on("shown.bs.modal", ".modal", function () {
        $(this).find("form").attr("autocomplete", "off");
    });
    // 每次都获取新的
    $("body").on("hidden.bs.modal", ".modal", function () {
        $(this).removeData("bs.modal");
        $(this).remove();

    });

    $(document).on('click', '.modal_action', function (event) {
        event.preventDefault();
        var self = $(this);
        var url = self.attr("href");

        $.get(url, function (resp) {
            if ( resp.redirect_url) {
                top.window.location.href = resp.redirect_url;
                return;
            }
            if ( resp.error_url) {
                top.window.location.href = resp.error_url;
                return;
            }
            var title = self.html();
            var html = '<div class="modal" id="normal_modal">' +
                '<div class="modal-dialog">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
                '<h4 class="modal-title">' + title + '</h4>' +
                '</div>' +

                '<div class="modal-body">' +
                resp +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';


            $("#normal_modal").remove();
            $("body").append(html);
            $("#normal_modal").modal('show');

        });

        return false;

    });


    //  $('.datetime').datetimepicker();

    $(document).on('submit', ".ajax_form", function (event) {
        event.preventDefault();
        var self = $(this);
        var url = self.attr("action");
        self.ajaxSubmit({
            success: function (resp, status, xhr) {

                if (resp.error_code < 0) {
                    self.find(".error_reason").html(resp.error_reason);
                    self.find(".error_reason").show();
                }

                if (resp.redirect_url) {
                    top.window.location.href = resp.redirect_url;
                    return;
                }
                if (resp.error_url) {
                    top.window.location.href = resp.error_url;
                    return;
                }
            }
        });

        return false;
    });

    $(document).on('submit', '.ajax_model_form', function (event) {
        event.preventDefault();
        var self = $(this);
        var url = self.attr("action");
        var model = self.data("model");
        if (!model) {
            alert('没有定义data-model');
            return;
        }
        var action = "edit";
        if (url.match(/create/i)) {
            action = "create";
        }
        startWait();
        self.ajaxSubmit({
            error: function (xhr, status, error) {
                stopWait();
                alert('服务器错误 ' + error);
            },

            success: function (resp, status, xhr) {

                stopWait();
                if (resp.redirect_url) {
                    top.window.location.href = resp.redirect_url;
                    return;
                }
                if (resp.error_url) {
                    top.window.location.href = resp.error_url;
                    return;
                }
                if (resp.error_code < 0) {
                    self.find(".error_reason").html(resp.error_reason);
                    self.find(".error_reason").show();
                } else {

                    var tpl = $("#" + model + "_tpl").html();
                    if (tpl && resp[model]) {
                        var compiled_tpl = juicer(tpl);
                        var html = compiled_tpl.render(resp);


                        if (action == "edit") {
                            $("#" + model + "_" + resp[model].id).replaceWith(html);
                        } else {
                            var list = $('#' + model + '_list');
                            
                            if (list.length < 1){
                                list = $('#_list');
                            }

                            if (list.hasClass("fix_top")) {
                                list.find(":first").after(html);
                            } else {
                                list.prepend(html);
                            }
                        }
                    }
                    self.parents('.modal').modal("hide");
                }

            }
        });

        return false;

    });


    $('.ajax_action').click(function (event) {
        event.preventDefault();
        var self = $(this);

        $.ajax({url: self.attr("href")}).done(function (resp) {
            if (resp.redirect_url) {
                top.window.location.href = resp.redirect_url;
                return;
            }
            if (resp.error_url) {
                top.window.location.href = resp.error_url;
                return;
            }

            if (0 != resp.error_code) {
                alert(resp.error_reason);
            } else {
                var model = self.parents("[data-model]").data('model');
                $.each(resp, function (k, v) {

                    $('#' + model + "_" + resp.id + "_" + k).html(v);
                });

                alert('操作成功');
            }
        });
    });

    $(document).on('click', '.delete_action', function (event) {
        event.preventDefault();
        if (confirm('确定删除?')) {

            var self = $(this);
            var url = self.attr("href");
            $.get(url, function (resp) {
                if (resp.redirect_url) {
                    top.window.location.href = resp.redirect_url;
                    return;
                }

                if (resp.error_url) {
                    top.window.location.href = resp.error_url;
                    return;
                }
                if (resp.error_code == 0) {
                    $(self.data("target")).remove();
                } else {
                    alert(resp.error_reason);
                }


            });
        }
        return false;
    });


    $(".submit_btn").click(function (event) {

        event.preventDefault();

        $(this).parents("form").ajaxSubmit({
            success: function (resp) {
                if (resp.redirect_url) {
                    top.window.location.href = resp.redirect_url;
                    return;
                }
                if (resp.error_url) {
                    top.window.location.href = resp.error_url;
                    return;
                }

                if (0 != resp.error_code) {

                    alert(resp.error_reason);
                } else {
                    alert('操作成功');
                    //window.href=window.href;
                    location.reload();
                }
                $('.ui.modal').modal('hide');
            }
        });
    });

    $('.once_click').click(function (event) {
        event.preventDefault();
        var self = $(this);
        var url = self.attr("href");
        $.get(url, function (resp) {
            if (resp.redirect_url) {
                top.window.location.href = resp.redirect_url;
                return;
            }
            if (resp.error_url) {
                top.window.location.href = resp.error_url;
                return;
            }
            alert(resp.error_reason);
        });
        return false;
    });

    $("body").on('submit','.page_form', function(event) {

        event.preventDefault();
        action = $(this).attr('action');
        page  = $(this).find('[name=page]').val();
        page = parseInt(page);
        if (isNaN(page)){
            page = 1;
        }
        if (action.indexOf('?') > 0){
            action += '&page=' + page;
        }
        target = $(this).parents('.ajax_content');
        if (target.length > 0){

            $.get(action,function(resp){
                target.html(resp);
            });
            return;
        }
        location.href = action;
        return false;
    });
});
