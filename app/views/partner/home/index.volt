<div class="bg"></div>
<div class="container">
    <div class="line bouncein">
        <div class="xs6 xm4 xs3-move xm4-move">
            <div style="height:150px;"></div>
            <div class="media media-y margin-big-bottom">
            </div>
            <div class="panel loginbox">
                <div class="login_title"><h1>Hi语音公会-登录</h1></div>
                <div class="panel-body ">
                    <img src="{{ qrcode }}" class="login_qr" alt="">
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    var opts = {
        data: {
            agreement: true,
            upload_status: false
        },
        methods: {}
    };

    vm = XVue(opts);

    function refresh() {

        $.post("/partner/home/check_auth", {}, function (resp) {

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