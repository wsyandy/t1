<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>嗨到停不下来</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/wap/css/sem1.css">
    <script src="/js/jquery/1.11.2/jquery.min.js"></script>
</head>
<body>
<div class="voice_box">
    <img src="/wap/images/sem1_text.png" alt="" class="img_text">
    <a class="btn_box" href="#" id="download" data-url="{{download_url}}">
        <img src="/wap/images/sem1_button.png" alt="" class="button">
    </a>
</div>
<script>
    $(function () {
        $("#download").click(function (){
            var url = $(this).data('url');
            console.log(url);
            location.href = url;
        });
    });
</script>
</body>
</html>
