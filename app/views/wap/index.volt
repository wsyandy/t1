<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <title>Hi-语音交友</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <style type="text/css">
        body, div, a, p {
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            width: 100%;
            font-size: calc(100vw / 7.5);
        }

        html {
            font-size: calc(100vw / 7.5);
            background: #fcac46;
        }

        /*****推广页******/
        .expand_box {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .expand_bg {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            width: 100%;
            height: 100%;
            position: relative;
        }

        .btn_bg {
            position: absolute;
            bottom: 0.26rem;
            left: 50%;
            margin-left: -2.01rem;
            width: 4.02rem;
            height: 1.45rem;
        }
    </style>
</head>
<body>
<div class="expand_box">
    <img class="expand_bg" src="images/expand_bg2.png" alt="">
    <a href="{{ file_url }}">
        <img class="btn_bg" src="images/btn_bg.png" alt="">
    </a>
</div>
</body>
</html>