<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>我要提现</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/m/css/money_style.css">
    <script src="/js/jquery/1.11.2/jquery.min.js"></script>
    <script src="/js/utils.js"></script>
</head>
<body>
<div class="get_money">
    <h3>总共可提现金额：{{ amount }}元</h3>
</div>
<div class="get_money_wrap">
    <h2>提现金额</h2>
    <div class="money_num">
        <span>￥</span>
        <input type="text" name="" placeholder="满10元可提现，且需为整数" id="money">
    </div>
</div>
<div class="get_money_input">
    <ul>
        <li>
            <span>姓名</span>
            <input type="text" name="" placeholder="请输入您的姓名" id="name">
        </li>
        <li>
            <span>支付宝账号</span>
            <input type="text" name="" placeholder="请输入您的支付宝账户" id="account">
        </li>
    </ul>
</div>
<div class="get_text">
    <p>每月1-5号提现，3～10个工作日到账</p>
</div>
<div class="get_btn">
    <a href="#">提交</a>
</div>
<script type="text/javascript">
    $(function () {
        $('.get_btn a').click(function () {
            $(this).addClass('changecolor');
            create();
        })
    });

    function create() {
        var money = $("#money").val();
        console.log(money);
        var name = $("#name").val();
        var account = $("#account").val();
        var data = {
            sid: "{{ sid }}",
            code: "{{ code }}",
            money: money,
            name: name,
            account: account
        };

        $.authPost("/m/withdraw_histories/create", data, function (resp) {
            alert(resp.error_reason);
        })
    }
</script>
</body>
</html>
