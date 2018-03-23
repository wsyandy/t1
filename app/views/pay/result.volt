<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ title }}</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/wx/yuewan/css/common.css">
    <link rel="stylesheet" href="/wx/yuewan/css/pay_result.css">

    {{ weixin_js('/js/jquery/1.11.2/jquery.min.js', '/js/vue/2.0.5/vue.min.js', '/js/utils.js') }}

    {{ block_holder('head') }}

</head>

<script>
    window.alert = function (name) {
        var iframe = document.createElement("IFRAME");
        iframe.style.display = "none";
        iframe.setAttribute("src", 'data:text/plain,');
        document.documentElement.appendChild(iframe);
        window.frames[0].window.alert(name);
        iframe.parentNode.removeChild(iframe);
    };
</script>

<body style="background-color: #f2f3f7;">

<div id="app" style="height: 100%;" v-cloak>
    <div class="haeder_nav">
        <span class="haeder_left_back" @click="backAction()"></span>
        <span>{{title}}</span>
        <span class="haeder_right_text"></span>
    </div>

    <div class="main_content">
        <div class="topup_select_money">

            {% if order is defined and order.isPaid() %}
                <span class="topup_results_successful"></span>
                <p class="topup_results_title">恭喜您，{{order.order_type_text}}成功</p>
            {% else %}
                <span class="topup_results_failure"></span>
                <p class="topup_results_title">很抱歉，{{order.order_type_text}}失败</p>
            {% endif %}
        </div>

    </div>

    <script>
        var opts = {
            data: {

            },
            methods: {
                backAction:function () {
                    window.history.back();
                }

            }
        };
        var vm = XVue(opts);

    </script>
</div>

</body>
</html>


