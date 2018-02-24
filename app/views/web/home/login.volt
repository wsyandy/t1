{{ block_begin('head') }}
<link rel="stylesheet" href="/tm/css/apple.css">
{{ block_end() }}
<div class="login_background">
    <div class="login_background">
        <div class="login_box">
            <div class="login_box_title">
                <span class="login_sweep_icon"></span>
                <span>扫码登录</span>
            </div>
            <div class="login_box_content">
                <img class="login_QRcode" src="{{ qrcode }}" alt="">
                <span>使用考拉微课APP扫描二维码登录</span>
            </div>
        </div>
    </div>
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