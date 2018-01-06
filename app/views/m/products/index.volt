<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>我的账户</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/css/products.css">
</head>
<body>
<div class="account_top">
    <p>钻石余额：<span>{{ user.diamond }}</span></p>
</div>
<div class="account_money">
    <ol>
        {% for product in products %}
            {% if (product.id == selected_product.id) %}
            <li class="selected" data-product_id="{{ product.id }}">
            {% else %}
                <li data-product_id="{{ product.id }}">
            {% endif %}
            <span>钻石{{ product.diamond }}</span>
            <span>¥{{ product.amount }}</span>
            </li>
        {% endfor %}
    </ol>
</div>
<div class="account_pay">
    <ul>
        {% for payment_channel in payment_channels %}
            {% if (payment_channel.id == selected_payment_channel.id) %}
            <li class="selected_pay" data-payment_channel_id="{{ payment_channel.id }}" data-payment_type="{{ payment_channel.payment_type }}">
            {% else %}
                <li data-payment_channel_id="{{ payment_channel.id }}" data-payment_type="{{ payment_channel.payment_type }}">
            {% endif %}
            {{ payment_channel.name }}</li>
        {% endfor %}
    </ul>
</div>

<div class="get_out_btn">
    <a href="/m/orders/create?sid={{ user.sid }}&payment_channel_id={{ selected_payment_channel.id }}&product_id={{ selected_product.id }}&payment_type={{ selected_payment_channel.payment_type }}" id="pay_submit_btn" class="account_btn">确定</a>
</div>

<script src="/js/jquery/1.11.2/jquery.min.js"></script>
<script type="text/javascript">
    function generatePayUrl() {
        var product_id = $(".selected").data('product_id');
        var selected_pay = $(".selected_pay");
        var payment_channel_id = selected_pay.data('payment_channel_id');
        var pay_submit = $("#pay_submit_btn");
        var original_pay_url = "/m/orders/create?sid=" + '{{ user.sid }}';
        var payment_type = selected_pay.data('payment_type');
        var pay_url = original_pay_url + "&product_id=" + product_id + "&payment_channel_id=" + payment_channel_id + "&payment_type=" + payment_type;
        pay_submit.attr('href', pay_url);
    }

    $(function () {
        // 钻石选择
        $('.account_money ol li').each(function () {
            $(this).click(function () {
                $(this).addClass('selected').siblings().removeClass('selected');
                generatePayUrl();
            })
        });
        // 支付方式选择
        $('.account_pay ul li').each(function () {
            $(this).click(function () {
                $(this).addClass('selected_pay').siblings().removeClass('selected_pay');
                generatePayUrl();
            })
        })
    })
</script>
</body>
</html>
