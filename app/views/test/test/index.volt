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
    var ws_server = 'ws://local.chance.com/websocket/';
    // var ws_server = 'ws://116.62.103.161:9509?user_id=1';
    var web_socket = new WebSocket("ws://ws.test.com");
    web_socket.onopen = function (evt) {
        alert("connect successful!");
        // msg.innerHTML = web_socket.readyState;
    }

    function send() {
        var text = document.getElementById('text').value;
        document.getElementById('text').value = '';

        var data = '{a: 1, b: 2}';
        web_socket.send(data);
    }

    web_socket.onmessage = function (evt) {
        console.log(evt.data);
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