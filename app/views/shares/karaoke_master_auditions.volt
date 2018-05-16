<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ title }}</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/shares/css/share_work1.css">
</head>
<body>
<img class="share_bg" src="/shares/images/share_work_bg.png" alt="">
<img src="/shares/images/right.png" class="share_tips none" alt="" id="open_in_browser_tip">

<div class="share_box">
    <div class="share_main">
        <div class="share_avatar">
            <img src="/shares/images/karaoke.png" alt="">
        </div>
        <div class="share_user">
            <div class="share_user_name">Hi语音官方助手</div>
            <div class="share_user_id">ID:666666</div>
        </div>
        <div class="share_text">
            报名参加<歌神争霸赛>，赢取冠军专属演唱会特权、千元现金大奖，围观更有百元礼包现场抽取！

            <img class="quotes_left" src="/shares/images/quotes_left.png" alt="">
            <img class="quotes_right" src="/shares/images/quotes_right.png" alt="">
        </div>
        <span class="line_left"></span>
        <span class="line_right"></span>
    </div>
    <div class="pro_info">
        <img class="logo_hi" src="/shares/images/logo_hi.png" alt="">
        <div class="pro_text">
            Hi_很好玩的语音直播软件
        </div>
    </div>

    <a href="{{ code }}://" id="jump" class="jump">立即下载</a>

</div>

<!-- 弹框 开始-->
<div class="fudong">
    <h3>您还未安装 <b>Hi语音 </b>，无法直接进入App，请先去下载</h3>
    <div class="btn_list">
        <span class="close_btn">取消</span>
        <span><a href="/soft_versions/index?id={{ soft_version_id }}" class="close_right">去下载</a></span>
    </div>
</div>

<script src="/js/jquery/1.11.2/jquery.min.js"></script>
<script src="/js/utils.js"></script>
<script src="/shares/js/index.js"></script>

<script>

    $(document).ready(function () {

        if ($.isWeixinClient() || $.isWeiboClient()) {

            $("#open_in_browser_tip").removeClass('none');

        } else {

            $(".jump").click(function () {

                if ({{ soft_version_id }}) {
                    setTimeout(showTip, 2000);
                }

            });
        }

        $(".close_right").click(function () {
            $(".fudong").hide();
            $(".fudong_bg").hide();
        });
    });

</script>
</body>
</html>

