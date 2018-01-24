<html>
<head>
    <title>测试swoole</title>
    <script src="/js/jquery/1.11.2/jquery.min.js"></script>
</head>
<body>

<input type="text" id="text">
<input type="submit" value="发送数据" id="send">

<script>

    var msg = document.getElementById('msg');
    var ws_server = 'ws://alive.momoyuedu.cn';
    // var ws_server = 'ws://116.62.103.161:9509?user_id=1';
    var web_socket = new WebSocket(ws_server);
    web_socket.onopen = function (evt) {
        alert("connect successful!");
        // msg.innerHTML = web_socket.readyState;
    }

    function send() {
        var text = document.getElementById('text').value;
        document.getElementById('text').value = '';

        // var data = "{'name':'hello'}";
        // web_socket.send(data.parseJSON());
    }

    web_socket.onmessage = function (evt) {
        console.log(evt);
    }

    web_socket.onerror = function (evt, e) {
        alert('Error occured: ' + evt.data);
    }

    $("#send").click(function () {
        send();
    });
</script>
</body>
</html>