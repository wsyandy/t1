var selected_room_id = '';
var sid;
var code;

$(function () {

    $('.room_cover').hide();

    $(".room_out").on("click", function () {
        $("#password").val('');
        $(".room_cover").hide();
    });

    $(".room_in").on("click", function () {
        var url = "/m/unions/check_password";
        var password = $('#password').val();

        console.log(selected_room_id);

        var data = {sid: sid, code: code, password: password, room_id: selected_room_id};
        $.authPost(url, data, function (resp) {
            if (resp.error_code == 0) {
                var url = "app://rooms/detail?id=" + selected_room_id;
                location.href = url;
                $(".room_cover").fadeOut();
            } else {
                alert(resp.error_reason);
            }
        });
    });
});