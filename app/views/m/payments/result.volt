
{% if order is defined and order.isPaid() %}
    恭喜您，支付成功
{% else %}
    支付失败
{% endif %}