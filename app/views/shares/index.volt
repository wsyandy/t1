<!DOCTYPE html>
<html lang="en" style="background:#6f4dda">
<head>
    <meta charset="UTF-8">
    <title>分享</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/shares/css/index.css">
</head>
<body style="background:#6f4dda">
<div class="share_box">
    <img src="/shares/images/share_bg.png" class="share_bg">
    <img src="/shares/images/right.png" class="share_right none" id="open_in_browser_tip">
    <div class="share_person">
        <div class="share_pic">
            <img src="{{ user.avatar_small_url }}">
        </div>
        <h3>{{ user.nickname }}</h3>
        <p>ID：{{ user.id }}</p>
        <a href="" class="upload_btn" id="jump_room">进入Ta的房间</a>
    </div>
</div>
<div class="share_bottom">
    <div class="left">
        <div class="share_logo">
            <img src="/shares/images/logo.png">
        </div>
        <div class="logo_hi">
            <h3>Hi_很好玩的语音直播软件</h3>
            <p>连麦聊天，组队开黑哦~</p>
        </div>
    </div>
    <div class="right">
        <a href="" class="upload_btn" id="jump">立即下载</a>
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
            $("#jump_room").attr('href', '{{ user.product_channel.code }}://enter_room?room_id={{ room_id }}');
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
                    app_url += '?room_id=' + room_id;
                }

                window.location = app_url;

                if (soft_version_id) {
                    setTimeout(showTip, 2000);
                }
            });

            $("#jump").click(function (e) {
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
    })

    function Download() {
        window.location = "/soft_versions/index?id=" + {{  soft_version_id }};
    }
</script>

</body>
</html>
