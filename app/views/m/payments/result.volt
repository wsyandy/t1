<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>支付状态</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/m/css/result.css">

</head>
<body>
<div class="pay_state">
    {% if payment.pay_status==0 %}
    <!-- 支付确认中开始 -->
    <img src="/m/images/queren.png">
    <h3>支付确认中...</h3>
    <p>耐心一点，猴急的样子一定不美腻</p>
    <!-- 支付确认中结束 -->
    {% elseif payment.pay_status==1 %}
    <!-- 支付成功开始 -->
    <img src="/m/images/chenggong.png">
    <h3>恭喜，支付成功啦～</h3>
    <!-- 支付成功结束 -->
    {% elseif payment.pay_status==2 %}
    <!-- 支付失败开始 -->
    <img src="/m/images/shibai.png">
    <h3>哎呦，支付失败了～</h3>
    <p>努力一点，不要让缘分擦肩而过</p>
    <!-- 支付失败中结束 -->
    {% endif %}
</div>
</body>
</html>
