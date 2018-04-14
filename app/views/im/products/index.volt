{# 支付页面模板. 我的账户跟产品页面引用 #}
{{ block_begin('head') }}
{{ theme_css('/im/css/main.css','/im/css/recharge.css') }}
{{ theme_js('/js/fastclick.js') }}
{{ block_end() }}


<div class="vueBox">
    <div class="recharge_top">
        <div class="recharge_head">
            <div class="recharge_head_title">
                <img class="icon_diamonds" src="/im/images/icon_diamonds.png" alt="">
                <span>我的金币</span>
            </div>
            <div class="recharge_head_num">{{ user.i_gold }}</div>
        </div>
    </div>
    <div class="recharge">
        <ul id="account_money">
            {% for product in products %}
                {% if (product.id == selected_product.id) %}
                    <li data-product_id="{{ product.id }}" class="option select">
                        <div class="diamonds"><span>{{ product.getShowIGold(user) }}</span>金币</div>
                        <div class="dollar"><span>{{ product.amount }}</span>$</div>
                        <img class="icon_select" src="/im/images/icon_selected.png" alt="">
                    </li>
                {% else %}
                    <li data-product_id="{{ product.id }}" class="option">
                        <div class="diamonds"><span>{{ product.getShowIGold(user) }}</span>金币</div>
                        <div class="dollar"><span>{{ product.amount }}</span>$</div>
                        <img class="icon_select" src="/im/images/icon_selected.png" alt="" style="display: none;">
                    </li>
                {% endif %}
            {% endfor %}
        </ul>
        <div class="payment">
            <span class="">支付方式</span>
        </div>
        <div class="account_money_select">
            {% for payment_channel in payment_channels %}
                {% if (payment_channel.id == selected_payment_channel.id) %}
                    <span class="cur" data-payment_channel_id="{{ payment_channel.id }}"
                          data-payment_type="{{ payment_channel.payment_type }}"
                          id="payment_type_{{ payment_channel.payment_type }}">{{ payment_channel.name }}</span>
                {% else %}
                    <span data-payment_channel_id="{{ payment_channel.id }}"
                          data-payment_type="{{ payment_channel.payment_type }}">{{ payment_channel.name }}</span>
                {% endif %}
            {% endfor %}

        </div>

        <div class="recharge_btn" data-href="/im/payments/create?sid={{ user.sid }}&payment_channel_id={{ selected_payment_channel.id }}&product_id={{ selected_product.id }}&payment_type={{ selected_payment_channel.payment_type }}&code={{ product_channel.code }}">
            确认充值
        </div>

    </div>
</div>

<script type="text/javascript">
    function generatePayUrl() {
        var product_id = $(".select").data('product_id');
        var payment_channel = $(".cur");
        var payment_channel_id = payment_channel.data('payment_channel_id');
        var payment_type = payment_channel.data('payment_type');
        var url = "/im/payments/create?sid={{ user.sid }}&payment_channel_id=" + payment_channel_id + "&payment_type=" + payment_type + "&product_id=" + product_id + "&code={{ product_channel.code }}";
        if (payment_channel_id == undefined) {
            url = $(".recharge_btn").data("href");
        }
        return url;
    }

    $(function () {
        FastClick.attach(document.body);
        //只有苹果支付的时候,隐藏苹果支付选项
        if ($(".account_pay li").length <= 1) {
            $("#payment_type_apple").hide();
        }

        // 国际版金币选择
        $('#account_money li').each(function () {

            $(this).click(function () {
                $(this).addClass('select');
                $(this).siblings().removeClass('select');

                $(this).find('.icon_select').show();
                $(this).siblings().find('.icon_select').hide();
                var url = generatePayUrl();
                $(".recharge_btn").data('href', url);
            });

        });

        // 支付方式选择
        $('.account_money_select span').each(function () {
            $(this).click(function () {

                $(this).addClass('cur');
                $(this).siblings().removeClass('cur');

                var url = generatePayUrl();
                $(".recharge_btn").data('href', url);
            })
        });

        $('.recharge_btn').click(function () {
            var href = $(this).data('href');
            location.href = href;
        });
    })
</script>


