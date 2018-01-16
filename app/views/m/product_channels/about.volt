<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>关于我们</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/m/css/style.css">
</head>
<body>
<div class="about_us_top">
    <img src="{{ product_channel.avatar_small_url }}">
    <h3>约玩</h3>
</div>
<div class="about_us_list">
    <ul>
        <div class="banben">
            <span>当前版本</span>
            <b>{{ version }}</b>
        </div>
        <li>
            <a href="/m/product_channels/user_agreement?code={{ product_channel.code }}">用户协议<span class="arrow_right"></span></a>
        </li>
        <li>
            <a href="/m/product_channels/privacy_agreement?code={{ product_channel.code }}">隐私条款<span class="arrow_right"></span></a>
        </li>
    </ul>
</div>
<script src="/js/jquery/1.11.2/jquery.min.js"></script>
</body>