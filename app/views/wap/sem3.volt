<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ product_channel_name }}</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/wap/css/apple.css">
    <link rel="stylesheet" href="/wap/css/sms_sem.css">
    <script src="/js/jquery/1.11.2/jquery.min.js"></script>
</head>
<body style="background-color: #f2f3f7;">

<div style="display: flex;width:100%;height:100%;" id="download" data-url="{{ download_url }}">
    <img style="width:100%;height:100%;" src="/wap/images/sem3_bg.jpg" alt="">
</div>

<script>
    $("#download").click(function () {
        var url = $(this).data('url');
        location.href = url;
    })
</script>

</body>
</html>