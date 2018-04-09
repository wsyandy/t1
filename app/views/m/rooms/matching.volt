{{ block_begin('head') }}
{{ theme_css('/m/rooms/css/main.css','/m/rooms/css/matching.css') }}
{{ block_end() }}

<div class="matching">
    <div class="matching_title"> 匹配中，预计等待时间<span class="matching_time"></span>s</div>
    <div class="ripple">
        <div class="dot">
            <div class="dot2">
                <div class="dot3">
                    <img class="avatar" src="{{ user.avatar_url }}" alt="">
                </div>
            </div>
        </div>
    </div>
    <div class="matching_btn">取消</div>
</div>


<script>

    var time = document.querySelector(".matching_time");
    console.log(time.innerHTML);

    function countdown(second) {
        // 秒数
        var second = Math.floor(second);
        // 时间格式化输出，如11:03 25:19 每1s都会调用一次
        second = toTwo(second);
//         console.log('剩余时间：' + second)
        var timer = setTimeout(function () {
            if (second > 0) {
                second -= 1;
                countdown(second)
            } else {
                clearTimeout(timer)
                location.href = "app://home";
            }
        }, 1000);
        time.innerHTML = second
    }

    /**
     * 封装函数使1位数变2位数
     */
    function toTwo(n) {
        n = n < 3 ? "0" + n : n;
        return n
    }

    var second = 3;
    countdown(second);


    function refresh() {
        data = {
            sid: '{{ sid }}',
            code: '{{ code }}'
        };

        $.post("/m/rooms/matching", data, function (resp) {
            if (resp.error_url) {
                window.location.href = resp.error_url;
                clearInterval(timer);
            }
        })
    }

    var timer = setInterval(refresh, 1000);


    $(".matching_btn").click(function () {
        location.href = "app://home";
    });

</script>
