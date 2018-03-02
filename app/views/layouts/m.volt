<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ title }}</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <script src="/js/jquery/1.11.2/jquery.min.js"></script>
    <script src="/js/vue/2.0.5/vue.min.js"></script>
    <script src="/js/utils.js"></script>
    {{ block_holder('head') }}
</head>
<body>
{{ content() }}
</body>
</html>