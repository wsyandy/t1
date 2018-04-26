<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>管理后台</title>
    {{ js('/js/jquery/1.11.2/jquery.min.js','/js/vue/2.0.5/vue.min.js','/js/jquery.form/3.51.0/jquery.form.js',
    '/framework/bootstrap.select/1.11.2/js/bootstrap-select.min.js',
    '/framework/bootstrap/3.3.4/js/bootstrap.min.js',
    '/framework/bootstrap.datepicker/1.5.0/js/bootstrap-datepicker.min.js',
    '/framework/bootstrap.datepicker/1.4.0/js/bootstrap-datetimepicker.min.js',
    '/framework/bootstrap.datepicker/1.4.0/locales/bootstrap-datetimepicker.zh-CN.js',
    '/js/juicer/0.6.9/juicer-min.js','/js/echarts/2.2.7/echarts.js','/js/admin.js',
    '/framework/bootstrap.select/1.11.2/js/i18n/defaults-zh_CN.min.js') }}

    {{ css('/framework/bootstrap/3.3.4/css/bootstrap.min.css','/framework/bootstrap.datepicker/1.4.0/css/bootstrap-datetimepicker.min.css',
    '/framework/bootstrap.datepicker/1.5.0/css/bootstrap-datepicker.min.css','/css/admin.css', '/framework/bootstrap.select/1.11.2/css/bootstrap-select.min.css') }}

</head>
<body>

<nav class="navbar navbar-default navbar-static-top {% if is_development %}dev_navbar{% endif %}" role="navigation"
     style="padding-left: 10px;padding-right: 10px;">

    <ul class="nav navbar-nav navbar-right">
        <li><a href="/partner/home/logout">注销</a></li>
    </ul>

</nav>
<div style="padding:0 15px;">
    {{ content() }}
</div>

</body>
</html>