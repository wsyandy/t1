{{ block_begin('head') }}
{{ theme_css('/web/css/main','/web/css/style') }}
{{ block_end() }}

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