{# 支付页面模板. 我的账户跟产品页面引用 #}
{{ block_begin('head') }}
{{ theme_css('/m/css/product.css') }}
{{ theme_js('/js/fastclick.js') }}
{{ block_end() }}

<div class="account_top">
    <p>钻石余额：<span>{{ user.diamond }}</span></p>
    <div class="top_text">(钻石是用来送礼物的)</div>
</div>
<div class="account_money">
    <ol>
        {% for product in products %}
            <li data-product_id="{{ product.id }}">
                {% if (product.id == selected_product.id) %}
                    <span class="select_color selected_color">钻石{{ product.getShowDiamond(user) }}</span>
                    <span>¥{{ product.amount }}</span>
                    <b class="select selected"></b>
                {% else %}
                    <span class="select_color">钻石{{ product.getShowDiamond(user) }}</span>
                    <span>¥{{ product.amount }}</span>
                    <b class="select"></b>
                {% endif %}
            </li>
        {% endfor %}
    </ol>
</div>
<div class="account_pay">
    <ul>
        {% for payment_channel in payment_channels %}
            {% if (payment_channel.id == selected_payment_channel.id) %}
                <li data-payment_channel_id="{{ payment_channel.id }}"
                    data-payment_type="{{ payment_channel.payment_type }}"
                    id="payment_type_{{ payment_channel.payment_type }}">
                    <span>{{ payment_channel.name }}</span>
                    <i class="selected_pay select_pay"></i>
                </li>
            {% else %}
                <li data-payment_channel_id="{{ payment_channel.id }}"
                    data-payment_type="{{ payment_channel.payment_type }}">
                    <span>{{ payment_channel.name }}</span>
                    <i class="select_pay"></i>
                </li>
            {% endif %}
        {% endfor %}
    </ul>
</div>
{% if !is_foreign_ip %}
    <div style="padding: 11px; text-align: center; color: #3C3C3C">优惠充值关注公众号Hi-6888</div>
{% endif %}
<div class="get_out_btn">
    <a href="/m/payments/create?sid={{ user.sid }}&payment_channel_id={{ selected_payment_channel.id }}&product_id={{ selected_product.id }}&payment_type={{ selected_payment_channel.payment_type }}&code={{ product_channel.code }}"
       id="pay_submit_btn" class="account_btn">确定</a>
</div>
<script type="text/javascript">
    function generatePayUrl() {
        var product_id = $(".selected").parent().data('product_id');
        var payment_channel = $(".selected_pay").parent();
        var payment_channel_id = payment_channel.data('payment_channel_id');
        var payment_type = payment_channel.data('payment_type');
        var url = "/m/payments/create?sid={{ user.sid }}&payment_channel_id=" + payment_channel_id + "&payment_type=" + payment_type + "&product_id=" + product_id + "&code={{ product_channel.code }}";
        if (payment_channel_id == undefined) {
            url = $("#pay_submit_btn").attr("href");
        }
        return url;
    }

    $(function () {
        FastClick.attach(document.body);
        //只有苹果支付的时候,隐藏苹果支付选项
        if ($(".account_pay li").length <= 1) {
            $("#payment_type_apple").hide();
        }
        // 钻石选择
        $('.account_money ol li').each(function () {
            $(this).click(function () {
                $(this).find('.select').addClass('selected');
                $(this).siblings().find('.select').removeClass('selected');

                $(this).find('.select_color').addClass('selected_color');
                $(this).siblings().find('.select_color').removeClass('selected_color');
                var url = generatePayUrl();
                $("#pay_submit_btn").attr('href', url);
            })
        });
        // 支付方式选择
        $('.account_pay ul li').each(function () {
            $(this).click(function () {
                $(this).find('.select_pay').addClass('selected_pay');
                $(this).siblings().find('.select_pay').removeClass('selected_pay');
                var url = generatePayUrl();
                $("#pay_submit_btn").attr('href', url);
            })
        });
    })
</script>
