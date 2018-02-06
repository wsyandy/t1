<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>领取记录</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/m/css/money_style.css">
    <script src="/js/jquery/1.11.2/jquery.min.js"></script>
</head>
<body>
<div class="get_top">累计领取：{{ total_money }}元</div>
<div class="main_content">
    {% if  flag %}
        {% for history in withdraw_histories %}
            <ul class="get_details_ul">
                <li>
                    <div class="top">
                        <p>金额</p>
                        <span>2017-12-08</span>
                    </div>
                    <div class="bottom">
                        <span>{{ history.amount }}</span>
                        <b>{{ history.status_text }}</b>
                    </div>
                </li>
            </ul>
        {% endfor %}
    {% else %}
        <div class="get_none">
            <img src="/m/images/get_none.png">
            <p>天哪，您还没有领取记录！</p>
        </div>
    {% endif %}

</div>
</body>
</html>
