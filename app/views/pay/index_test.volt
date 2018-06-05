<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>大额充值</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    {{ theme_css('/pay/css/style_product.css') }}
    {{ weixin_js('/js/jquery/1.11.2/jquery.min.js') }}
    {{ theme_js('/js/vue/2.0.5/vue.min.js', '/js/pub_pop.js', '/js/fastclick.js', '/js/clipboard.min.js', '/js/utils.js') }}
</head>
<body>

<div id="app">
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
        <input required="required" type="text" class="name_input" id="user_id" placeholder="请输入您的Hi~ID"/>
        <i class="close_btn" @click="closeBtn"></i>
        <p class="name"></p>
    </div>
    <div class="weixin_title">选择充值金额</div>
    <div class="weixin_cz_list">
        <ul>

            {% for product in products %}

                <li class="product_{{ product.id }}"
                    @click="product('{{ product.id }}', '{{ product.amount }}')">
                    <h2>
                        <i></i>
                        <span>{{ product.getShowDiamond('') }}</span>
                    </h2>

                    {% set send_diamond = product.getShowSendDiamond(product.full_name) %}

                    {% if (send_diamond != '' or product.gold != '') %}
                        <p>
                            {% if send_diamond != '' %} 送钻石{{ send_diamond }} {% endif %}
                            {% if (send_diamond != '' and product.gold != '') %} + {% endif %}
                            {% if product.gold != '' %} 送金币{{ product.gold }} {% endif %}
                        </p>
                    {% endif %}

                </li>

            {% endfor %}

        </ul>
    </div>
    <div class="money_box">套餐金额：<span>￥<span id="change_amount"></span></span></div>
    <div class="money_pay_list">
        <ul>

            {% set pay_type = ['weixin_h5':'weixin', 'alipay_h5':'zhifubao'] %}

            {% for payment_channel in payment_channels %}

                <li class="payment_channel_{{ payment_channel.id }}"
                    @click="paymentChannel('{{ payment_channel.id }}', '{{ payment_channel.payment_type }}')">

                    <i class="{{ pay_type[payment_channel.payment_type] }}"></i>{{ payment_channel.name }}
                    <b></b>
                </li>
            {% endfor %}

        </ul>
    </div>
    <div class="money_pay_question" style="margin-top:100px;">
        <h3><i></i>温馨提示</h3>
        <p>请到微信公众号：Hi-6888关注最新充值活动信息</p>
    </div>
</div>

<script type="text/javascript">
    $(function () {

        var options = {
            data: {
                pay_amount: 0,
                product_id: 0,
                payment_channel_id: 0,
                user_id: 0,
                form_status: 0
            },
            methods: {

                product: function (id, amount) {
                    var user_id = $('#user_id').val(),
                        self = $('.product_' + id);

                    if (!user_id) {
                        vm.tips('请填写正确的HI ID');
                        return false;
                    }

                    if (vm.user_id != user_id) {

                        vm.user_id = user_id;
                        $.authPost('/pay/check_user', {'user_id': user_id}, function (response) {
                            $(".name").text(response.nickname);
                        });
                    }

                    vm.user_id = user_id;
                    vm.pay_amount = amount;
                    vm.product_id = id;
                    self.addClass('weixin_cz_selected').siblings().removeClass('weixin_cz_selected');
                    vm.changeAmount();
                },

                paymentChannel: function (id) {
                    if (!vm.product_id) {
                        vm.tips('请选择钻石!');
                        return false;
                    }

                    vm.payment_channel_id = id;
                    var self = $('.payment_channel_' + id);
                    self.addClass('zhifubao_pay_li').siblings().removeClass('zhifubao_pay_li');


                    if (vm.form_status == 1) {
                        return false;
                    }

                    var data = {
                        payment_channel_id: vm.payment_channel_id,
                        product_id: vm.product_id,
                        user_id: vm.user_id
                    };
                    vm.form_status = 1
                    $.authPost('/pay/create', data, function (response) {
                        if (response.error_code == 0) {
                            location.href = response.url;
                        } else
                            alert(response.error_reason);
                    });
                },

                changeAmount: function () {
                    $('#change_amount').text(vm.pay_amount);
                },

                tips: function (string) {
                    alert(string);
                },

                closeBtn: function () {
                    $('.name_input').val('');
                }

            }
        }

        var vm = new XVue(options);
        vm.changeAmount();

        if ($.isWeixinClient()) {
            $('.zhifubao_pay_t').show();
        }
    })
</script>
</body>
</html>
