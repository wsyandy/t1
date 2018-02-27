<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta content="width=device-width,initial-scale=1,user-scalable=no,shrink-to-fit=no" name="viewport">
    <meta content="webkit" name="renderer">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="format-detection" content="telephone=no">
    <meta content="email=no" name="format-detection">
    <title>扫码登录</title>
    <link rel="stylesheet" href="/web/css/main.css">
    <link rel="stylesheet" type="text/css" href="/web/css/style.css">
    <script src="/js/jquery/1.11.2/jquery.min.js"></script>
</head>
<body>
<header>
    <div class="wrapper">
        <span class="logo_icon"></span>
        <h2>Hi~</h2>
        <ul>
            <li><a href="/">首页</a></li>
            <li><a href="/upload.html" class="nav_selected">上传音乐</a></li>
        </ul>
    </div>
</header>
<div class="qrcode_login">
    <h2>扫码登录，防止被盗</h2>
    <img src="{{ qrcode }}">
    <h3>使用HI语音交友手机版扫描二维码</h3>
    <p>在Hi~软件里-->侧边栏-->设置-->扫一扫，打开扫一扫功能</p>
</div>

<script>
    function refresh() {
        $.post("/web/home/check_auth", {}, function (resp) {
            if (resp.error_url) {
                location.href = resp.error_url;
            }
        })
    }

    setInterval(refresh, 1000);
</script>

</body>
</html>