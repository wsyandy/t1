
{{ block_begin('head') }}
{{ weixin_js('pay.js') }}
{{ theme_css('/wx/yuewan/css/style_product.css') }}
{{ block_end() }}

<div id="app">
    <div class="weixin_warn_box">
        <div class="wran">
            <i></i>
            <div id="error_reason">请填写正确的HI ID</div>
        </div>
    </div>

    <div class="weixin_chongzhi_top">
        <input required="required" type="text" class="name_input" id="user_id" placeholder="请输入您的Hi~ID" />
        <i class="close_btn" @click="closeBtn()"></i>
        <p class="name"></p>
    </div>
    <div class="weixin_title">选择充值金额</div>
    <div class="weixin_cz_list_01">
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

                    {% set tamp_gold_egg = product.getParseFieldData(product.data, 'tamp_gold_egg') %}
                    {% if tamp_gold_egg != '' %}
                        <b>赠砸蛋{{ tamp_gold_egg }}次</b>
                    {% endif %}
                </li>

            {% endfor %}


        </ul>
    </div>
    <div class="money_box">套餐金额：<span>￥<span id="change_amount"></span></span></div>
    <div class="max_money">砸蛋最高可获得100000钻</div>
    <div class="money_pay_list">
        <ul>
            <li class="zhifubao_pay_li payment_channel_{{ selected_payment_channel.id }}" @click="paymentChannel('{{ selected_payment_channel.id }}', '{{ selected_payment_channel.payment_type }}')">
                <i class="weixin"></i>微信
                <b></b>
            </li>
        </ul>
    </div>
    <div class="money_pay_question">
        <h3><i class="wenti"></i>常见问题</h3>
    </div>
</div>

<script type="text/javascript">
    $(function(){

        var options = {
                data: {
                    pay_amount: 0,
                    product_id: 0,
                    user_id: 0,
                    default_payment_type: 'weixin_js'
                },
                methods: {

                    product: function (id, amount) {

                        vm.pay_amount = amount;
                        vm.product_id = id;

                        var self = $('.product_'+id);
                        self.addClass('weixin_cz_selected').siblings().removeClass('weixin_cz_selected');
                        vm.changeAmount();
                    },

                    paymentChannel: function (id, payment_type) {

                        var self = $('.payment_channel_'+id),
                            user_id = $('#user_id').val();

                        if (!user_id) {
                            vm.tips('请填写正确的HI ID');
                            return false;
                        }

                        if (!vm.product_id) {
                            vm.tips('请选择钻石!');
                            return false;
                        }

                        if (!id || !payment_type) {
                            vm.tips('请选择支付渠道');
                            return false;
                        }

                        var data = {
                            user_id: user_id,
                            payment_channel_id: id,
                            payment_type: payment_type,
                            product_id: vm.product_id
                        };
                        $.authPost('/wx/payments/create', data, function (response) {

                            if (response.error_code == 0 && response.payment_type == vm.default_payment_type) {

                                redirect_url = '/wx/payments/result?order_no=' + response.order_no;
                                js_api_parameters = response.form;
                                wxPay();
                            } else {
                                response.error_code != 0 && vm.tips(response.error_reason);
                                response.payment_type != vm.default_payment_type && vm.tips(response.payment_type);
                            }
                        });

                        self.addClass('zhifubao_pay_li').siblings().removeClass('zhifubao_pay_li');
                    },

                    changeAmount: function () {
                        $('#change_amount').text(vm.pay_amount);
                    },

                    tips: function (string) {
                        $("#error_reason").html(string);
                        $('.weixin_warn_box').fadeIn();
                        setTimeout(function () {
                            $('.weixin_warn_box').fadeOut(1500);
                        }, 800)
                    },
                    
                    closeBtn: function () {
                        $('.name_input').val('');
                    }
                }
            },
            vm = XVue(options);
            vm.changeAmount();

    })
</script>