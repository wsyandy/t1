<html>
<head>
    <title>测试swoole</title>
</head>
<body>

<div id="msg" style="width:400px;height:200px;background-color:  tomato;overflow-x: scroll;overflow-y: scroll;box-shadow: 0px 0px 3px gray;"></div>
<input type="text" id="text">
<input type="submit" value="发送数据">

<script>

    var msg = document.getElementById('msg');
    // var ws_server = 'ws://127.0.0.1:9509?user_id=1';
    var ws_server = 'ws://116.62.103.161:9509?user_id=1';
    var web_socket = new WebSocket(ws_server);
    web_socket.onopen = function (evt) {
        alert("connect successful!");
        // msg.innerHTML = web_socket.readyState;
    }

    // function song() {
    //     var text = document.getElementById('text').value;
    //     document.getElementById('text').value = '';
    //     web_socket.send(text);
    // }

    web_socket.onmessage = function (evt) {
        console.log(evt.data);
        msg.innerHTML += evt.data + '<br>';
    }

    web_socket.onerror = function (evt,e) {
        alert('Error occured: ' + evt.data);
    }

</script>
</body>
</html>