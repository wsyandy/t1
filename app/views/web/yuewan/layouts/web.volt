<!DOCTYPE html>
<html lang="en">
<head>
    <title>Hi语音</title>
    <link rel="shortcut icon" href="/web/images/logo.png"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta content="width=device-width,initial-scale=1,user-scalable=no,shrink-to-fit=no" name="viewport">
    <meta content="webkit" name="renderer">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="format-detection" content="telephone=no">
    <meta content="email=no" name="format-detection">
    <link rel="stylesheet" href="/web/css/main.css">
    <script src="/js/jquery/1.11.2/jquery.min.js"></script>
    <script src="/js/vue/2.0.5/vue.min.js"></script>
    <script src="/js/utils.js"></script>
    {{ block_holder('head') }}
</head>
<body>

<div class="vueBox" id="app" v-cloak>
    <header>
        <div class="wrapper">
            <span class="logo_icon"></span>
            <h2>Hi~</h2>
            <ul>
                <li><a href="/web/home/index">首页</a></li>
                <li><a href="/web/users/index"
                       {% if controller_name == 'users' and action_name == 'index' %}class="nav_selected" {% endif %} >上传音乐</a>
                </li>
                {% if show_logout %}
                    <li><a href="/web/home/logout" class="get_out">退出 <i></i></a></li>
                {% endif %}
                <li><a href="#" id="download_simulator_apk">Android模拟器专用版</a></li>
            </ul>
        </div>
    </header>

    {{ content() }}
</div>

<script>
    $("#download_simulator_apk").click(function (e) {

        e.preventDefault();

        $.authGet('/web/home/simulator_apk', function (resp) {
            if (resp.error_url) {
                location.href = resp.error_url;
            }
        })
    })
</script>
</body>
</html>