<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>微信</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/pay/css/style.css">
    {{ weixin_js('/js/jquery/1.11.2/jquery.min.js', '/js/vue/2.0.5/vue.min.js', '/js/utils.js') }}

</head>
<body id="app" style="height: 100%;" v-cloak>
<!-- 支付宝支付提示 -->
<div class="zhifubao_pay_t">
    <div class="share_box">
        <h2>只需两步即可选择支付宝支付：</h2>
        <img src="/pay/images/share.png" class="share">
        <div class="share_text">
            <p><i class="one"></i>点击右上角的<img src="/pay/images/share_icon.png" class="share_icon">按钮</p>
            <p><i class="two"></i>选择 <img src="/pay/images/icon.png" class="icon"></p>
        </div>
    </div>
</div>
<div class="weixin_chongzhi_top">
    <input required="required" type="text" class="name_input" placeholder="请输入您的Hi~ID" />
    <i class="close_btn"></i>
    <p class="name"> 此ID不存在</p>
</div>
<div class="weixin_title">选择充值金额</div>
<div class="weixin_cz_list">
    <ul>
        {% for product in products %}
            <li id="" data-amount="{{ product.amount }}" @click="rechargeAction({{ product.id }})">
                <i></i>
                <span>{{ product.diamond }}</span>
            </li>
        {% endfor %}
    </ul>
</div>
<div class="money_box">套餐金额：<span>￥30</span></div>
<div class="money_pay_list">
    <ul>
        {% for payment_channel in payment_channels %}
            <li class="zhifubao_pay_li" data-payment_channel_id="{{ payment_channel.id }}"
                data-payment_type="{{ payment_channel.payment_type }}"
                id="payment_type_{{ payment_channel.payment_type }}">
                <i class="zhifubao"></i>{{ payment_channel.name }}
                <b></b>
            </li>
        {% endfor %}
    </ul>
</div>
<div class="money_pay_question">
    <h3><i></i>温馨提示</h3>
    <p>请到微信公众号：Hi-6888关注最新充值活动信息</p>
</div>
<script src="/pay/js/jquery.min.js"></script>
<script type="text/javascript">
    $(function(){
        $('.weixin_cz_list ul li').each(function(){
            $(this).click(function(){
                $(this).addClass('weixin_cz_selected').siblings().removeClass('weixin_cz_selected');

                $('.money_pay_list').addClass('selected_pay');

                var $tree = $(this).data('amount');

            })
        });

        $('.close_btn').click(function(){
            $('.name_input').val('');
        });

//        $('.zhifubao_pay_li').click(function(){
//            $('.zhifubao_pay_t').show();
//        })
//        $('.zhifubao_pay_t').click(function(){
//            $(this).hide();
//        })

    });



    var opts = {
        data: {
            payment_channel_id: '{{ selected_payment_channel.id }}',
            payment_type: "{{ selected_payment_channel.payment_type }}",
            submit_status: false,
            nickname: "{{ pay_user_name }}"
        },
        methods: {
            rechargeAction: function (id) {

                if (this.submit_status) {
                    return false;
                }

                var user_id = $('#user_id').val();

                if (!user_id) {
                    smsTip('请填写正确的HI ID');
                    return;
                }

                var product_id = id;
                if (!product_id) {
                    smsTip('请选择钻石!');
                    return;
                }

                if (!this.payment_channel_id || !this.payment_type) {
                    smsTip('请选择支付渠道');
                    return;
                }

                var data = {
                    'user_id': user_id,
                    'payment_channel_id': this.payment_channel_id,
                    'payment_type': this.payment_type,
                    'product_id': product_id
                };

                this.submit_status = true;
                $.authPost('/pay/create', data, function (resp) {
                    vm.submit_status = false;
                    vm.nickname = resp.nickname;
                    if (0 == resp.error_code) {
                        redirect_url = '/pay/result?order_no=' + resp.order_no;
                        js_api_parameters = resp.form;
                        if ('weixin_js' == resp.payment_type) {//微信支付
                            wxPay();
                        } else {
                            alert(resp.payment_type);
                        }
                    } else {
                        alert(resp);
                    }
                });

            }
        }
    };
    var vm = new XVue(opts);

</script>
</body>
</html>
