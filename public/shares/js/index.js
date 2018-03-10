;

function colse_fd() {
    $(".fudong").hide();
    $(".fudong_bg").hide();
};

$(".fudong").hide();
$(".fudong_bg").hide();
var doc_height = $(document).height();
var w_height = $(window).height();
var w_width = $(window).width();

function showTip() {
    $(".fudong").show();
    $(".fudong_bg").show();

    $(".fudong_bg").attr("style", "height:" + doc_height + "px");
    var div_width = $(".fudong").width();
    var div_height = $(".fudong").height();

    var div_left = w_width / 2 - div_width / 2 + "px";
    var div_top = w_height / 2 - div_height / 2 + "px";

    $(".fudong").css({
        "left": div_left,
        "top": div_top
    });

    $(".upload_btn").removeAttr('disabled')
}


// $(".upload_btn").click(function (e) {

//
//     e.preventDefault();
//
//     if ($.isWeixinClient()) {
//         $(".share_right").removeClass('none');
//         return;
//     }
//
//     var code = $("#code").val();
//     var app_url = code + '://start_app';
//
//     var soft_version_id = $("#soft_version_id").val();
//     var room_id = $("#room_id").val();
//
//     if (room_id) {
//         app_url += '?room_id=' + room_id;
//     }
//
//     window.location = app_url;
//
//     if (soft_version_id) {
//         setTimeout(function () {
//             window.location = "/soft_versions/index?id=" + soft_version_id;
//         }, 2000);
//     }
// })


$(".close_btn").click(function () {
    colse_fd();
});

