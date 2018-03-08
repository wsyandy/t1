{{ block_begin('head') }}
{{ theme_css('/m/css/withdraw_histories.css') }}
{{ block_end() }}

<div class="get_money">
    <h3>总共可提现金额：{{ amount }}元</h3>
</div>
<div class="get_money_wrap">
    <h2>提现金额 <span>（满50元可提现，且需为整数）</span></h2>
    <div class="money_num">
        <span>￥</span>
        <input type="number" name="" id="money">
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

    var amount = {{ amount }};

    function create() {

        var money = $("#money").val();
        var name = $("#name").val();
        var account = $("#account").val();

        if (money > amount) {
            $('.get_btn a').removeClass('changecolor');
            return alert("请输入正确的提现金额");
        }

        var data = {
            sid: "{{ sid }}",
            code: "{{ code }}",
            money: money,
            name: name,
            account: account
        };

        $.authPost("/m/withdraw_histories/create", data, function (resp) {
            alert(resp.error_reason);
            $('.get_btn a').removeClass('changecolor');
        })
    }
</script>

