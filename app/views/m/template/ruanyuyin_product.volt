{# 软语音 支付页面模板. 我的账户跟产品页面引用 #}
{{ block_begin('head') }}
{{ theme_css('/m/css/ruanyuyin_product.css') }}
{{ theme_js('/js/font_rem.js') }}
{{ block_end() }}
<div id="app">
    <div class="wo_chongzhi_bg">
        <p><i></i>我的钻石</p>
        <h2>{{ user.diamond }}</h2>
    </div>
    <div class="wo_chongzhi_list">
        {% if products|length >0 %}
            <ul>
                {% for k, product in products %}
                    <li :class="{money_selected: {{ intval(k) }} == 0}" id="product_{{ k }}"
                        @click="selectProduct('{{ k }}', '{{ product.id }}')">
                        <b>{{ product.diamond }}钻石</b>
                        <p>{{ product.amount }}元</p>
                        <i></i>
                        {% if product.gold %}
                            <span>送{{ product.gold }}金币</span>
                        {% endif %}
                    </li>
                {% endfor %}
            </ul>
        {% endif %}
    </div>
    <div class="pay_money">
        {#//支付方式 优惠充值 关注公众号 HI-6888#}
        <h3></h3>
        <ul>
            {% for k, payment_channel in payment_channels %}
                <li :class="{pay_selected: {{ intval(k) }} == 0}" id="payment_channel_{{ k }}"
                    @click="selectPaymentChannel('{{ k }}', '{{ payment_channel.id }}', '{{ payment_channel.payment_type }}')">

                    {% if in_array(payment_channel.payment_type, ['weixin_h5', 'weixin', 'weixin_js']) %}
                        <b class="weixin"></b>
                    {% endif %}

                    {% if in_array(payment_channel.payment_type, ['alipay_sdk', 'alipay_h5']) %}
                        <b class="zhifubao"></b>
                    {% endif %}

                    <span>{{ payment_channel.name }}</span>
                    <i></i>
                </li>
            {% endfor %}
        </ul>
    </div>

    <div class="pay_btn" @click="pay()">
        <span>确认充值</span>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        var opts = {
            data: {
                product_id: '{{ selected_product.id }}',
                payment_channel_id: '{{ selected_payment_channel.id }}',
                payment_type: '{{ selected_payment_channel.payment_type }}'
            },
            watch: {},
            methods: {
                selectProduct: function (target, product_id) {
                    $("#product_" + target).addClass('money_selected').siblings().removeClass('money_selected');
                    vm.product_id = product_id;
                },
                selectPaymentChannel: function (target, payment_channel_id, payment_type) {
                    $("#payment_channel_" + target).addClass('pay_selected').siblings().removeClass('pay_selected');
                    vm.payment_channel_id = payment_channel_id;
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
                }
            }
        };

        var vm = XVue(opts);
    })

</script>



