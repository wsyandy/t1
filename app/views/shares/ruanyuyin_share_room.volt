<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HI语音直播</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/shares/css/ruanyuyin_share_room_apple.css">
    <link rel="stylesheet" href="/shares/css/ruanyuyin_share_room.css">
</head>
<body>
<div class="vueBox">
    <img class="bg" src="/shares/images/bg.png" alt="">
    <div class="share_tips">戳右上角，在浏览器中打开</div>
    <div class="share_box">
        <div class="share_avatar">
            <img src="{{ user.avatar_url }}" alt="">
        </div>
        <div class="share_name">{{ user.nickname }}</div>
        <div class="share_id">ID: <span>{{ user.id }}</span></div>
        <div class="share_title">我正在这个房间玩，快来一起连麦嗨~</div>
        <a class="share_enter" id="jump_room"> 进入TA的房间 </a>
    </div>
    <div class="footer">
        <img class="logo_hi_radius" src="/shares/images/ruanyuyin_logo_hi.png" alt="">
        <span class="footer_txt">软语音-很好玩的语音直播软件</span>
        <a class="download" href="#" id="jump">立即下载 </a>
    </div>
</div>
<input type="hidden" id="code" value="{{ user.product_channel.code }}"/>
<input type="hidden" id="soft_version_id" value="{{ soft_version_id }}"/>
<input type="hidden" id="room_id" value="{{ room_id }}"/>

<!-- 弹框 开始-->
<div class="fudong">
    <h3>您还未安装 <b>Hi语音 </b>，无法直接进入Ta的房间，请先去下载</h3>
    <div class="btn_list">
        <span class="close_btn">取消</span>
        <span class="close_right">去下载</span>
    </div>
</div>

<div class="fudong_bg"></div>
<!-- 弹框结束 -->
<script src="/js/jquery/1.11.2/jquery.min.js"></script>
<script src="/js/utils.js"></script>
<script src="/shares/js/index.js"></script>

<script>
    $(document).ready(function () {

        if ($.isWeixinClient() || $.isWeiboClient()) {
            $("#open_in_browser_tip").removeClass('none');
        } else if ($.isQqClient()) {
            $("#jump_room").attr('href', '{{ user.product_channel.code }}://enter_room?room_id={{ room_id }}&user_id={{ user.id }}');
            $("#jump").attr('href', "/soft_versions/index?id=" + {{ soft_version_id }});
        } else {

            $("#jump_room").click(function (e) {
                e.preventDefault();

                if ('disabled' == $(this).attr('disabled')) {
                    return;
                }

                $(this).attr('disabled', 'disabled');

                var code = $("#code").val();
                var app_url = code + '://enter_room';
                var soft_version_id = $("#soft_version_id").val();
                var room_id = $("#room_id").val();

                if (room_id) {
                    app_url += '?room_id=' + room_id + "&user_id=" + {{ user.id }};
                }

                console.log(app_url);


                window.location = app_url;

                if (soft_version_id) {
                    setTimeout(showTip, 2000);
                }
            });

            $("#jump").click(function (e) {
                e.preventDefault();

                var code = $("#code").val();
                var app_url = code + '://enter_room';
                var soft_version_id = $("#soft_version_id").val();
                var room_id = $("#room_id").val();

                if (room_id) {
                    app_url += '?room_id=' + room_id;
                }

                window.location = app_url;

                if (soft_version_id) {
                    setTimeout(Download, 2000);
                }
            });
        }

        $(".close_right").click(function () {
            Download()
        })
    });

    function Download() {
        window.location = "/soft_versions/index?id=" + {{ soft_version_id }};
    }
</script>
</body>
</html>