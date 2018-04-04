{{ block_begin('head') }}
{{ theme_css('/m/css/withdraw_histories.css', '/m/css/pop.css') }}
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
            <input type="text" name="" placeholder="请输入您的姓名" id="name" value="{{ user_name }}">
        </li>
        <li>
            <span>支付宝账号</span>
            <input type="text" name="" placeholder="请输入您的支付宝账户" id="account" value="{{ alipay_account }}">
        </li>
    </ul>
</div>
<div class="get_text">
    <p>1.1Hi币=1人民币。</p>
    <p>2.Hi币金额需大于或等于50元才可以提现。</p>
    <p>3.扶持期间提现无手续费，每周可提现一次，当周所提现的金额将在下周二到账。</p>
</div>
<div class="get_btn">
    <a href="#">提交</a>
</div>

<div class="fudong">
    <div class="title">
        <h2 id="title">温馨提示</h2>
    </div>
    <div class="fd_text" id="error_text">
        <p class="error_reason" id="error_reason"></p>
    </div>
    <div class="close_btn">知道了</div>
</div>
<div class="fudong_bg"></div>

<script type="text/javascript">
    $(function () {
        $('.get_btn a').click(function () {
            $(this).addClass('changecolor');
            create();
        })
    });

    var withdraw_amount = {{ amount }};
    var skip = false;

    function colse_fd() {
        $(".fudong").hide();
        $(".fudong_bg").hide();
    }

    function show_fd(error_reason) {
        $("#error_reason").text(error_reason);
        $(".fudong").show();
        $(".fudong_bg").show();
    }

    //    var doc_height = $(document).height();
    var w_height = $(window).height();
    var w_width = $(window).width();

    $(".fudong").hide();
    $(".fudong_bg").hide();

    //    $(".fudong_bg").attr("style", "height:" + doc_height + "px");
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
        if (skip === true) {
            location.href = '/m/withdraw_histories/index?sid={{ sid }}&code={{ code }}';
        }
    });

    function create() {

        var amount = $("#money").val();
        var name = $("#name").val();
        var account = $("#account").val();

        if (amount > withdraw_amount || amount <= 0) {
            $('.get_btn a').removeClass('changecolor');
            show_fd("请输入正确的提现金额");
            return;
        }

        var data = {
            sid: "{{ sid }}",
            code: "{{ code }}",
            amount: amount,
            name: name,
            account: account
        };

        $.authPost("/m/withdraw_histories/create", data, function (resp) {
            show_fd(resp.error_reason);
            if (resp.error_code == 0) {
                skip = true;
            }
            $('.get_btn a').removeClass('changecolor');
        })
    }
</script>

