<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>软语音直播分享页</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/shares/css/ruanyuyin_apple.css">
    <link rel="stylesheet" href="/shares/css/ruanyuyin_share_work.css">
</head>
<body>
<div class="vueBox">
    <img class="bg" src="/shares/images/bg.png" alt="">
    <div class="share_box">
        <div class="share_avatar">
            <img src="{{ user.avatar_url }}" alt="">
        </div>
        <div class="share_name">{{ user.nickname }}</div>
        <div class="share_id">ID: <span>{{ user.id }}</span></div>
        <div class="share_title">
            软语音是一个非常好玩的语音直播软件,推荐给你玩一玩,里面可以连麦聊天,组队开黑！
        </div>
        <img class="logo_hi" src="/shares/images/ruanyuyin_logo_hi.png" alt="">
        <div class="logo_hi_txt">软语音-好玩的语音直播软件</div>
        <span  id="down_load" class="share_download">立即下载</span>
    </div>

</div>
<script src="/js/jquery/1.11.2/jquery.min.js"></script>
<script src="/js/utils.js"></script>
<script src="/shares/js/index.js"></script>

<script>

    $(document).ready(function () {
        $("#down_load").click(function (e) {
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

