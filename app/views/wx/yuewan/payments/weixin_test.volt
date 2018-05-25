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
        <input required="required" type="text" class="name_input" id="user_id" value="{{ pay_user_id }}"
               placeholder="请输入您的Hi~ID"/>
        <i class="close_btn" @click="closeBtn()"></i>
        <p class="name">${nickname}</p>
    </div>
    <div class="weixin_title">选择充值金额</div>
    <div class="weixin_cz_list_01">
        <ul>

            {% for product in products %}

                <li class="product_{{ product.id }}"
                    @click="rechargeAction('{{ product.id }}', '{{ product.amount }}')">
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
    <div class="money_box">套餐金额：<span>￥<span id="change_amount">${product_amount}</span></span></div>
    <div class="max_money">砸蛋最高可获得100000钻</div>

    <div class="money_pay_question" @click="redirectAction('/wx/payments/questions')">
        <h3><i class="wenti"></i>常见问题</h3>
    </div>
</div>

<script type="text/javascript">

    $(function () {

        $('.close_btn').click(function () {
            $('.name_input').val('');
        });

        $('.name_input').focus(function () {
            $('.weixin_warn_box').fadeOut()
        });

        $('.weixin_cz_list ul li').each(function () {
            $(this).click(function () {
                $(this).children('.money').addClass('money_selected').parents().siblings().children('.money').removeClass('money_selected');
            })
        });

        $('.close_btn').click(function () {
            $('.name_input').val('');
        })
    });

    var options = {
        data: {
            payment_channel_id: '{{ selected_payment_channel.id }}',
            payment_type: "{{ selected_payment_channel.payment_type }}",
            submit_status: false,
            nickname: "{{ pay_user_name }}",
            product_amount: 0
        },
        methods: {
            rechargeAction: function (id, product_amount) {

                if (this.submit_status) {
                    return false;
                }

                var user_id = $('#user_id').val();
                var self = $('.product_' + id);
                self.addClass('weixin_cz_selected').siblings().removeClass('weixin_cz_selected');
                vm.product_amount = product_amount;

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

                $.authPost('/wx/payments/create', data, function (resp) {
                    vm.submit_status = false;
                    vm.nickname = resp.nickname;
                    if (0 == resp.error_code) {
                        redirect_url = '/wx/payments/result?order_no=' + resp.order_no;
                        js_api_parameters = resp.form;
                        if ('weixin_js' == resp.payment_type) {//微信支付
                            wxPay();
                        } else {
                            alert(resp.payment_type);
                        }
                    } else {
                        smsTip(resp.error_reason);
                    }
                });

            }
        }
    }

    var vm = new XVue(options);

    function smsTip(conetnt) {
        $("#error_reason").html(conetnt);
        $('.weixin_warn_box').fadeIn()
    }

</script>