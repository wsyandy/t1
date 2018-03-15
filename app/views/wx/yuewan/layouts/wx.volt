<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ title }}</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>

    {{ weixin_css('common') }}

    {{ weixin_js('/js/jquery/1.11.2/jquery.min.js', '/js/vue/2.0.5/vue.min.js', '/js/utils.js') }}

    {{ block_holder('head') }}

</head>
<body style="background-color: #f2f3f7;">

<div id="app" style="height: 100%;" v-cloak>
    {{ content() }}
</div>

</body>
</html>