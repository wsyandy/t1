<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>我的收益</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/m/css/money_style.css">
    <link rel="stylesheet" href="/m/css/pop.css">
    <script src="/js/jquery/1.11.2/jquery.min.js"></script>
</head>
<body>
<!-- 弹出层开始 -->
<div class="fudong">
    <div class="title">
        <h2>收益说明</h2>
    </div>
    <div class="fd_text">
        <p>1. 别人送你礼物可以得到Hi币</p>
        <span>转化比例：<b>1</b> 钻石 = <b>0.1</b> Hi币</span>
        <p class="p_mr">2. Hi币可以进行提现</p>
        <span>兑换比例：<b>1</b> Hi币 = <b>0.1</b> 元</span>
    </div>
    <div class="close_btn">知道了</div>
</div>
<div class="fudong_bg"></div>
<!-- 弹出层结束 -->
<div >
    <img src="/m/images/question.png" class="money_image"  id="question">
</div>

<div class="money_box">
    <ul>
        <li>
            <b>{{ hi_coins }}</b>
            <span>Hi 币</span>
        </li>
        <li>
            <b>{{ amount }}</b>
            <span>可领取 (元)</span>
        </li>
    </ul>
</div>
<div class="get_btn">
    <a href="/m/withdraw_histories/get_money?sid={{ sid }}&code={{ code }}">我要提现</a>
</div>
<div class="money_btn">
    <a href="/m/withdraw_histories/records?sid={{ sid }}&code={{ code }}">领取记录</a>
</div>

<script type="text/javascript">
    $(function () {

        function colse_fd() {
            $(".fudong").hide();
            $(".fudong_bg").hide();
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

    });

</script>
</body>
</html>