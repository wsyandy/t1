<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ title }}</title>
    <link rel="stylesheet" href="/m/activities/css/cp_main.css">
    <script>
        (function(doc, win) {
            var docEl = doc.documentElement,
                resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
                recalc = function() {
                    var clientWidth = docEl.clientWidth;
                    if (!clientWidth) return;
                    docEl.style.fontSize = 100 * (clientWidth / 750) + 'px';
                };

            if (!doc.addEventListener) return;
            win.addEventListener(resizeEvt, recalc, false);
            doc.addEventListener('DOMContentLoaded', recalc, false);
        })(document, window);
    </script>
</head>
<body>
<div id="app" class="ready_launch">
    <img class="ready_launch_banner" src="/m/activities/images/cp_banner.png" alt="">
    <div class="ready_launch_box_">
        <div class="box_center_bg">
            <img class="rlus" src="/m/activities/images/box_center_rlus.png" alt="">
            <div class="box_center_time">
                <b>如何结为情侣？</b>
                <span>5月20日 00:00正式上线，敬请期待！</span>
            </div>
        </div>
    </div>
    <div class="box_bottom_bg"></div>
</div>
</body>
</html>