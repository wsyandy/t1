<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hi语音</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/wap/css/index.css?ts=1">
    <script src="/js/jquery/1.11.2/jquery.min.js"></script>
</head>
<body>
<img class="bg" src="/wap/images/bg.png" alt="">
<div class="expand_hi">
    <div class="expand_head">
        <img  class="expand_logo" src="/wap/images/logo.png" alt="">
        <div class="expand_head_text">
            <div class="expand_head_tit">hi</div>
            <div class="expand_head_txt">
                <span>游戏</span>
                <span>娱乐</span>
                <span>连麦</span>
            </div>
        </div>
    </div>
    <div class="expand_list">
        <div class="expand_left_1">
            <img  class="expand_left_1_img" src="/wap/images/expand_left_1.png" alt="">
            <div class="expand_left_1_txt">
                <p>多人语音互动，</p>
                <p>畅聊停不下来，</p>
            </div>
        </div>
        <img  class="expand_right_1_img" src="/wap/images/expand_right_1.png" alt="">
    </div>

    <div class="expand_list">
        <img  class="expand_left_2_img" src="/wap/images/expand_left_2_img.png" alt="">
        <div class="expand_left_2">
            <img  class="expand_right_2_img" src="/wap/images/expand_right_2_img.png" alt="">

            <div class="expand_left_2_txt">
                <p>万人开黑好机友，</p>
                <p>实时对话不用手，</p>

            </div>
        </div>
    </div>

    <div class="expand_list">
        <div class="expand_left_1">
            <img  class="expand_left_1_img" src="/wap/images/expand_left_3_img.png" alt="">
            <img  class="ico_circle" src="/wap/images/ico_circle.png" alt="">

            <div class="expand_left_1_txt">
                <p>看女神直播，</p>
                <p>礼物互动刷起来，</p>

            </div>
        </div>
        <img  class="expand_right_3_img" src="/wap/images/expand_right_3.png" alt="">
    </div>

    <div class="btn" id="download" data-url="{{download_url}}">
        <img class="btn_bg" src="/wap/images/btn_bg.png" alt="">
        <span class="btn_txt">连麦互聊</span>
    </div>
</div>
<script>
    $(function () {
       $("#download").click(function (){
           var url = $(this).data('url');
           location.href = url;
       });
    });
</script>
</body>
</html>