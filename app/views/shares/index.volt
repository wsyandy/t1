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
    <img src="/shares/images/right.png" class="share_right none">
    <div class="share_person">
        <div class="share_pic">
            <img src="{{ user.avatar_small_url }}">
        </div>
        <h3>{{ user.nickname }}</h3>
        <p>ID：{{ user.id }}</p>
        <a href="" class="upload_btn" id="jump">进入Ta的房间</a>
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
        <a href="#" class="upload_btn">立即下载</a>
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
<a  id="jump_room" href="yuewan://start_app?room_id={{ room_id }}"></a>

<div class="fudong_bg"></div>
<!-- 弹框结束 -->
<script src="/js/jquery/1.11.2/jquery.min.js"></script>
<script src="/js/utils.js"></script>
<script src="/shares/js/index.js"></script>

<script>

    $(document).ready(function () {
        function from_mobile() {
            var reg =
                /(iPad|nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|meizu|miui|ucweb|UCBrowser|blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220|\(X11;)/i;
            return reg.test(navigator.userAgent);
        }

        var ua = navigator.userAgent.toLowerCase();
        if (from_mobile()) {
            if (ua.match(/iphone|ipod|ipad/i)) {
                //$("#jump").attr('href','https://itunes.apple.com/cn/app/hello-yu-yin-jiao-you/id885737901?l=en&mt=8');//ios下载链接
                $("#jump").attr('href', '：http://android.myapp.com/myapp/detail.htm?apkName=com.yuewan.main&amp;amp;ADTAG=mobileu');//自动识别，跳转IOS，还是和安卓
            } else {
                $("#jump").attr('href', '：http://android.myapp.com/myapp/detail.htm?apkName=com.yuewan.main&amp;amp;ADTAG=mobile');//android下载链接
            }
        }

    })


    $(window).load(function () {
        var url = $("#jump_room").attr('href');
        window.location.href = url;
    });
</script>

</body>
</html>
