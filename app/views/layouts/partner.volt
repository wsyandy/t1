<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta name="renderer" content="webkit">
    <title>Hi语音</title>
    <link rel="stylesheet" href="/partner/css/pintuer.css">
    <link rel="stylesheet" href="/partner/css/admin.css">
    <link rel="stylesheet" href="/partner/css/login.css">
    <link rel="stylesheet" href="/partner/css/jedate-select.css">
    <link rel="stylesheet" href="/partner/css/jedate.css">
    <script src="/js/jquery/1.11.2/jquery.min.js"></script>
    <script src="/js/jquery.form/3.51.0/jquery.form.js"></script>
    <script src="/js/vue/2.0.5/vue.min.js"></script>
    <script src="/js/utils.js"></script>
    <script src="/partner/js/pintuer.js"></script>
    <script src="/partner/js/jquery.jedate.min.js"></script>
    <script src="/partner/js/jedate-select.js"></script>
    {{ block_holder('head') }}
</head>
<body>
<div id="app" v-cloak="">
    {{ content() }}
</div>
</body>
</html>