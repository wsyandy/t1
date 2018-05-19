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
<script>
    //解决alert弹出网址
    window.alert = function (name) {
        var iframe = document.createElement("IFRAME");
        iframe.style.display = "none";
        iframe.setAttribute("src", 'data:text/plain,');
        document.documentElement.appendChild(iframe);
        window.frames[0].window.alert(name);
        iframe.parentNode.removeChild(iframe);
    };

    var ua = navigator.userAgent.toLowerCase();//获取浏览器的userAgent,并转化为小写——注：userAgent是用户可以修改的
    var isIos = (ua.indexOf('iphone') != -1) || (ua.indexOf('ipad') != -1);//判断是否是苹果手机，是则是true

    $(function () {
        if (isIos) {
            pushHistory();
        }
    });

    //解决ios后退无法刷新
    function pushHistory() {
        window.addEventListener("popstate", function (e) {
            alert();
            self.location.reload();
        }, false);
        var state = {
            title: "",
            url: "#"
        };
        window.history.replaceState(state, "", "#");
    }
</script>
</html>