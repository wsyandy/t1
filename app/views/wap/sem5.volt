<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>测试你的声音</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/wap/css/sem5_1.css">
    <link rel="stylesheet" href="/wap/css/sem5_2.css">
    <script src="/js/jquery/1.11.2/jquery.min.js"></script>
</head>
<body>
<div class="extend_box" id="app1" style="display:block">
    <img class="logo" src="/wap/images/sem5/logo.png" alt="">
    <img class="header_text" src="/wap/images/sem5/header_text.png" alt="">
    <img class="pie_chart" src="/wap/images/sem5/pie_chart.png" alt="">
    <a class="btn_test">
        <img class="ico_microphone" src="/wap/images/sem5/ico_microphone.png" alt="">
        <span class="btn_txt" id="cliTest"">点我测试</span>
    </a>
</div>

<div id="app2" style="display:none" data-url="{{download_url}}">
    <ul class="room_list">
        <li>
            <div class="text_box">语音聊天</div>
            <div class="img_box">
                <img class="img_avatar" src="/wap/images/sem5/avatar_1.png" alt="">
                <p class="img_title">加入聊天 </p>
            </div>
        </li>
        <li>

            <div class="text_box">语音开黑</div>
            <div class="img_box">
                <img class="img_avatar" src="/wap/images/sem5/avatar_2.png" alt="">
                <p class="img_title">组队开黑 </p>
            </div>
        </li>
        <li>
            <div class="text_box">语音唱歌</div>
            <div class="img_box">
                <img class="img_avatar" src="/wap/images/sem5/avatar_3.png" alt="">
                <p class="img_title">听TA唱歌 </p>
            </div>
        </li>
        <li>
            <div class="text_box">语音连麦</div>
            <div class="img_box">
                <img class="img_avatar" src="/wap/images/sem5/avatar_4.png" alt="">
                <p class="img_title">和TA连麦 </p>
            </div>
        </li>
        <li>
            <div class="text_box">语音通话</div>
            <div class="img_box">
                <img class="img_avatar" src="/wap/images/sem5/avatar_5.png" alt="">
                <p class="img_title">免费通话 </p>
            </div>
        </li>
        <li>
            <a class="add_box">
                <img class="img_add" src="/wap/images/sem5/avatar_add.png" alt="">
                <p class="img_add_btn">加入语音房间</p>
            </a>
        </li>
    </ul>
    <img class="logo_ico" src="/wap/images/sem5/logo.png" alt="">
</div>
<script src="/wap/js/resize.js"></script>
<script>
    $(function () {
        $("#cliTest").click(function () {
            $("#app1").hide();
            $("#app2").show();
        });

        $("#app2").click(function () {
            var url = $(this).data('url');
            location.href = url;
        });
    })


</script>

</body>
</html>