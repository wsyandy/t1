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

<script>
    window.alert = function (name) {
        var iframe = document.createElement("IFRAME");
        iframe.style.display = "none";
        iframe.setAttribute("src", 'data:text/plain,');
        document.documentElement.appendChild(iframe);
        window.frames[0].window.alert(name);
        iframe.parentNode.removeChild(iframe);
    };
</script>

<body style="background-color: #f2f3f7;">

<div id="app" style="height: 100%;" v-cloak>
    {{ content() }}
</div>

</body>
</html>