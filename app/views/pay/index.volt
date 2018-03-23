<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>大额支付</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/pay/css/style.css?t=2">
    {{ weixin_js('/js/jquery/1.11.2/jquery.min.js') }}

</head>
<body id="app" style="height: 100%;" v-cloak>
<!-- 支付宝支付提示 -->
<div class="zhifubao_pay_t">
    <div class="share_box">
        <h2>只需两步即可完成支付：</h2>
        <img src="/pay/images/share.png" class="share">
        <div class="share_text">
            <p><i class="one"></i>点击右上角的<img src="/pay/images/share_icon.png" class="share_icon">按钮</p>
            <p><i class="two"></i>选择 <img src="/pay/images/icon.png" class="icon"></p>
        </div>
    </div>
</div>
<div class="weixin_chongzhi_top">
    <input required="required" id="user_id"  type="text" class="name_input" placeholder="请输入您的Hi~ID" />
    <i class="close_btn"></i>
    <p class="name"></p>
</div>
<div class="weixin_title">选择充值金额</div>
<div class="weixin_cz_list">
    <ul>
        {% for index,product in products %}

            <li class="" id="" data-amount="{{ product.amount }}" data-product_id="{{ product.id }}">
                <i></i>
                <span>{{ product.diamond }}</span>
            </li>

        {% endfor %}
    </ul>
</div>
<div class="money_box">套餐金额：<span>￥<span class="amount">0</span></span></div>
<div class="money_pay_list">
    <ul>
        {% for payment_channel in payment_channels %}
            <li @click="rechargeAction({{ payment_channel.id }})" class="zhifubao_pay_li recharge" data-payment_channel_id="{{ payment_channel.id }}"
                data-payment_type="{{ payment_channel.payment_type }}"
                id="payment_type_{{ payment_channel.payment_type }}">
                {% if payment_channel.payment_type == 'weixin_h5' or payment_channel.payment_type == 'weixin' or  payment_channel.payment_type == 'weixin_js' %}
                    <i class="weixin"></i>{{ payment_channel.name }}
                {% else %}
                    <i class="zhifubao"></i>{{ payment_channel.name }}
                {% endif %}

                <b></b>
            </li>
        {% endfor %}
    </ul>
</div>

<div class="money_pay_question">
    <h3><i></i>温馨提示</h3>
    <p>请到微信公众号：Hi-6888关注最新充值活动信息</p>
</div>

<form action="/pay/create" id="payment_form" method="get" autocomplete="off">
    <input type="hidden" id="payment_channel_id" name="payment_channel_id" value="">
    <input type="hidden" id="product_id" name="product_id" value="">
    <input type="hidden" id="user_id_hid" name="user_id" value="">
</form>

<script src="/pay/js/jquery.min.js"></script>
<script type="text/javascript">
    $(function(){
        if (isWeiXin()) {
            $('.zhifubao_pay_t').show();
        }

        $('.money_pay_list').addClass('selected_pay');

        var amount = $('.weixin_cz_selected').data('amount');
        $(".amount").text(amount);

        var product_id = $('.weixin_cz_selected').data('product_id');
        $("#product_id").val(product_id);

        $('.weixin_cz_list ul li').each(function(){
            $(this).click(function(){
                //查找用户
                var user_id = $('#user_id').val();
                $.post('/pay/check_user', {'user_id': user_id}, function (resp) {
                    $(".name").text(resp.nickname);
                });

                $(this).addClass('weixin_cz_selected').siblings().removeClass('weixin_cz_selected');
                var amount = $(this).data('amount');
                $(".amount").text(amount);
                var product_id = $(this).data('product_id');
                $("#product_id").val(product_id);

            })
        });

        $('.recharge').click(function () {
            var user_id = $('#user_id').val();
            $("#user_id_hid").val(user_id);

            var payment_channel_id = $(this).data('payment_channel_id');
            $("#payment_channel_id").val(payment_channel_id);

            if (!user_id) {
                smsTip('请填写正确的HI ID');
                return;
            }

            var product_id = $('#product_id').val();
            if (!product_id) {
                smsTip('请选择钻石!');
                return;
            }

            if (!payment_channel_id) {
                smsTip('请选择支付渠道');
                return;
            }

            var form = $("#payment_form");
            var form_status = form.data('status');
            if (form_status == '1') {
                return;
            }

            form.data('status', '1');
            var data = form.serialize();
            $.post('/pay/create', data, function (resp) {
                form.data('status', '0');
                if (0 == resp.error_code) {
                    location.href = resp.url;
                } else {
                    alert(resp.error_code);
                }
            });
        });

        $('.close_btn').click(function(){
            $('.name_input').val('');
        });
        
//        $('.zhifubao_pay_t').click(function(){
//            $(this).hide();
//        })

    });

    function smsTip(conetnt) {
        alert(conetnt);
    }

    function isWeiXin() {
        var ua = window.navigator.userAgent.toLowerCase();
        if (ua.match(/MicroMessenger/i) == 'micromessenger') {
            return true;
        } else {
            return false;
        }
    }

</script>
</body>
</html>
