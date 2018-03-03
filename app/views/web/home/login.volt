{{ block_begin('head') }}
{{ theme_css('/web/css/style') }}
{{ block_end() }}

<div class="qrcode_login">
    <h2>扫码登录，防止被盗</h2>
    <h4 id="error_reason" style="margin: 10px;color:  red;"></h4>
    <img src="{{ qrcode }}">
    <h3>使用HI语音交友手机版扫描二维码</h3>
    <p>在Hi~软件里-->侧边栏-->设置-->扫一扫，打开扫一扫功能</p>
</div>

<script>
    function refresh() {
        $.post("/web/home/check_auth", {}, function (resp) {
            if (resp.error_code == -400) {
                console.log(resp.error_reason);
                $("#error_reason").html(resp.error_reason);
                clearInterval(timer);
            }
            if (resp.error_url) {
                location.href = resp.error_url;
            }
        })
    }
    var timer = setInterval(refresh, 1000);
</script>