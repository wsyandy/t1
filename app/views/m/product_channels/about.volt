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
            <b>V1.0</b>
        </div>
        <li>
            <a href="/m/product_channels/user_agreement?sid={{ sid }}&code={{ product_channel.code }}">用户协议</a>
        </li>
        <li>
            <a href="/m/product_channels/pri_agreement?sid={{ sid }}&code={{ product_channel.code }}">隐私条款</a>
        </li>
    </ul>
</div>
<script src="/js/jquery/1.11.2/jquery.min.js"></script>
</body>