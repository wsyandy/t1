{{ block_begin('head') }}
{{ theme_css('/m/css/style_product.css') }}
{{ theme_js('/js/pub_pop.js', '/js/fastclick.js', '/js/clipboard.min.js') }}
{{ block_end() }}

<style>

    .copy_tip {
        display: none;
        height: 50px;
        width: 70%;
        background-color: rgba(0,0,0,0.6);
        position: absolute;
        top: 50%;
        left: 15%;
        margin-top: -25px;
        color: #fff;
        text-align: center;
        line-height: 50px;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
    }
</style>
<div id="app">
    <div class="zuan_top">
        <div class="top">
            <i></i>
            <span>钻石余额：</span>
            <b>{{ user.diamond }}</b>
        </div>
        <p>（钻石是用来送礼物的）</p>
    </div>
    <div class="line_4"></div>
    <div class="weixin_title">选择充值金额</div>
    <div class="weixin_cz_list">
        <ul>

            {% for product in products %}

                <li class="{% if (product.id == selected_product.id) %} weixin_cz_selected {% endif %} product_{{ product.id }}"
                    @click="product('{{ product.id }}', '{{ product.amount }}')">
                    <h2>
                        <i></i>
                        <span>{{ product.getShowDiamond(user) }}</span>
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

    <!-- pay -->
    <div class="money_pay_list">
        <ul>

            {% set pay_type = ['weixin':'weixin', 'alipay_sdk':'zhifubao'] %}

            {% for payment_channel in payment_channels %}
                <li class="{% if (payment_channel.id == selected_payment_channel.id) %} zhifubao_pay_li {% endif %} payment_channel_{{ payment_channel.id }}" @click="payment_channel('{{ payment_channel.id }}', '{{ payment_channel.payment_type }}')">
                    <i class="{{ pay_type[payment_channel.payment_type] }}"></i>{{ payment_channel.name }}
                    <b></b>
                </li>
            {% endfor %}

        </ul>
    </div>

    <!-- 复制微信 -->
    <div class="youhui_weixin">
        <i></i>
        <span>优惠充值 关注公众号 <b>Hi-7899</b></span>
        <a href="javascript:;" class="btn_copy" data-clipboard-text="Hi-7899" @click="copy()">复制到微信</a>
    </div>
    <div class="copy_tip">复制成功</div>

    <!-- confirm button -->
    <div class="pay_btn" @click="pay()">
        <p>确定</p>
    </div>

    <!-- 免费赚钱 -->
    <div class="mine_money">
        <a href="/m/distribute?sid={{ user.sid }}&code={{ code }}">
            <i class="icon_money"></i>
            <span>免费赚钱</span>
            <i class="icon_right"></i>
        </a>
    </div>
    <div class="mine_text">充值问题咨询官方客服QQ：3407150190</div>
</div>

<script type="text/javascript">

    $(function(){


        var options = {
                data: {
                    pay_amount: '{{ selected_product.amount }}',
                    product_id: '{{ selected_product.id }}',
                    payment_channel_id: '{{ selected_payment_channel.id }}',
                    payment_type: '{{ selected_payment_channel.payment_type }}'
                },
                methods: {

                    copy: function () {

                        new ClipboardJS('.btn_copy');

                        var tip = $(".copy_tip");
                        tip.fadeIn();
                        tip.fadeOut(1000);

                        setTimeout(function () {
                            window.open("weixin://");
                        }, 700);
                    },
                    
                    product: function (id, amount) {
                        vm.pay_amount = amount;
                        vm.product_id = id;
                        var self = $('.product_'+id);
                        self.addClass('weixin_cz_selected').siblings().removeClass('weixin_cz_selected');
                        vm.change_amount();
                    },

                    payment_channel: function (id, payment_type) {

                        var self = $('.payment_channel_'+id);
                        self.addClass('zhifubao_pay_li').siblings().removeClass('zhifubao_pay_li');

                        vm.payment_channel_id = id;
                        vm.payment_type = payment_type;
                    },

                    pay: function () {

                        if (!vm.payment_channel_id || !vm.payment_type || !vm.product_id) {
                            alert("请选择正确的产品");
                            return;
                        }

                        var url = "/m/payments/create?sid={{ user.sid }}&payment_channel_id=" + vm.payment_channel_id
                            + "&product_id=" + vm.product_id + "&payment_type=" + vm.payment_type + "&code={{ product_channel.code }}";

                        window.location.href = url;

                        return;
                    },

                    change_amount: function () {
                        $('#change_amount').text(vm.pay_amount);
                    }
                }
            },
            vm = XVue(options);
            vm.change_amount();

    })


</script>
