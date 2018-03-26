<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>分享</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/shares/css/share_work.css">
</head>
<body>
<img class="share_bg" src="images/share_work_bg.png" alt="">
<img src="images/right.png" class="share_tips none" alt="" id="open_in_browser_tip">

<div class="share_box">
    <div class="share_main">
        <div class="share_avatar">
            <img src="{{ user.avatar_small_url }}" alt="">
        </div>
        <div class="share_user">
            <div class="share_user_name">{{ user.nickname }}</div>
            <div class="share_user_id">ID:{{ user.id }}</div>
        </div>
        <div class="share_text">
            老铁，Hi语音确实是一个非常好玩的语音
            直播软件，推荐给你玩一玩，里面可以连
            麦聊天，组队开黑
            <img class="quotes_left" src="images/quotes_left.png" alt="">
            <img class="quotes_right" src="images/quotes_right.png" alt="">
        </div>
        <span class="line_left"></span>
        <span class="line_right"></span>
    </div>
    <div class="pro_info">
        <img class="logo_hi" src="images/logo_hi.png" alt="">
        <div class="pro_text">
            Hi_很好玩的语音直播软件
        </div>
    </div>

    <div class="btn_download">
        <img class="btn_pink" src="images/btn_pink.png" alt="">
        <span>立即下载</span>
    </div>

</div>

<script src="/js/jquery/1.11.2/jquery.min.js"></script>
<script src="/js/utils.js"></script>
<script src="/shares/js/index.js"></script>

<script>

    $(document).ready(function () {

        if ($.isWeixinClient() || $.isWeiboClient()) {
            $("#open_in_browser_tip").removeClass('none');
        }

        $(".btn_download").click(function (e) {
            e.preventDefault();

            var app_url = '{{ user.product_channel.code }}' + '://';

            window.location = app_url;

            if ({{ soft_version_id }}) {
                setTimeout(Download, 2000);
            }
        });

    });

    function Download() {
        window.location = "/soft_versions/index?id=" + {{ soft_version_id }};
    }
</script>
</body>
</html>

