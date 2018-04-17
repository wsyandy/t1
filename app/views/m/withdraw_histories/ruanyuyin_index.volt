{{ block_begin('head') }}
{{ theme_css('/m/withdraw_histories/css/ruanyuyin_style.css', '/m/css/pop.css') }}
{{ block_end() }}
<!-- 弹出层开始 -->
<div class="fudong">
    <div class="title">
        <h2 id="title">收益说明</h2>
    </div>
    <div class="fd_text" id="error_text">
        <p class="error_reason" id="error_reason"></p>
    </div>
    <div class="close_btn">知道了</div>
</div>
<div class="fudong_bg"></div>
<!-- 弹出层结束 -->
<div class="money_box">
    <ul>
        <li>
            <b>{{ hi_coins }}</b>
            <span>R 币</span>
        </li>
        <li>
            <b>{{ amount }}</b>
            <span>可领取 (元)</span>
        </li>
    </ul>
</div>
{#操作#}
<div class="get_btn">
    <a href="/m/withdraw_histories/withdraw?sid={{ sid }}&code={{ code }}">我要提现</a>
</div>
{% if !is_height_version and show_exchange %}
<div class="get_btn last_btn">
    <a href="/m/hi_coin_histories/exchange?sid={{ sid }}&code={{ code }}">R币兑钻</a>
</div>
{% endif %}
<div class="money_btn">
    <a href="/m/withdraw_histories/records?sid={{ sid }}&code={{ code }}">领取记录</a>
</div>
<div class="get_money_text">
    <h3>收益说明</h3>
    <p>1、别人送你礼物可以得到R币</p>
    <p>2、R币可以进行提现：兑换比例 <span>1</span>R币= <span>1</span>元</p>
</div>
<script type="text/javascript">
    $(function () {

        function colse_fd() {
            $(".fudong").hide();
            $(".fudong_bg").hide();
            $("#title").text("收益说明");
            $("#declare").show();
            $("#error_text").hide();
        };

        function show_fd() {
            $(".fudong").show();
            $(".fudong_bg").show();
        };

        var doc_height = $(document).height();
        var w_height = $(window).height();
        var w_width = $(window).width();

        $(".fudong").hide();
        $(".fudong_bg").hide();
        $("#error_text").hide();

//        $(".fudong_bg").attr("style", "height:" + doc_height + "px");
        var div_width = $(".fudong").width();
        var div_height = $(".fudong").height();

        var div_left = w_width / 2 - div_width / 2 + "px";
        var div_top = w_height / 2 - div_height / 2 + "px";

        $(".fudong").css({
            "left": div_left,
            "top": div_top
        });

        $(".close_btn").click(function () {
            colse_fd();
        });

        $("#question").click(function () {
            show_fd();
        });

        $('.get_btn').on('click', "a", function (e) {
            e.preventDefault();
            var href = $(this).attr('href');
            $.post(href, '', function (resp) {
                console.log(resp.error_code)
                if (!resp.error_code) {
                    location.href = href;
                } else {
                    $("#title").text("温馨提示");
                    $("#error_reason").text(resp.error_reason);
                    $("#declare").hide();
                    $("#error_text").show();
                    show_fd();
                }
            });
        });

    });

</script>