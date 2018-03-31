var selected_user;


function close_fd() {
    $(".fudong").hide();
    $(".fudong_bg").hide();
    $(" #self_introduce").val('');
}

function open_fd() {
    $(".fudong").show();
    $(".fudong_bg").show();
}

function add_friend() {
    var url = "/m/users/add_friend";
    var self_introduce = $('#self_introduce').val();

    console.log(selected_user.id);

    var data = {sid: sid, code: code, self_introduce: self_introduce, user_id: selected_user.id};
    $.authPost(url, data, function (resp) {
    });
    selected_user.is_added = true;

}

function wordStatic(input) {
    var content = document.getElementById('num');
    if (content && input) {
        var value = input.value;
        value = value.replace(/\n|\r/gi, "");
        content.innerText = value.length;
    }
}

$(function () {

    var doc_height = $(document).height();
    var w_height = $(window).height();
    var w_width = $(window).width();

    $(".fudong_bg").attr("style", "height:" + doc_height + "px");
    var div_width = $(".fudong").width();
    var div_height = $(".fudong").height();

    var div_left = w_width / 2 - div_width / 2 + "px";
    var div_top = w_height / 2 - div_height / 2 + "px";

    $(".fudong").css({
        "left": div_left,
        "top": div_top
    });

    close_fd();

    $(".close_btn").click(function () {
        add_friend();
        close_fd();
    });

    $(".fudong_bg").click(function () {
        close_fd();
    })

});