{{ block_begin('head') }}
{{ theme_css('/m/css/gaive_diamond_by_cucumber.css') }}
{{ block_end() }}

<div id="handwritten" class="handwritten">
    <span class="handwritten_top_bg"></span>
    <div class="handwritten_state">
          <span>  <p id="hr"></p>:
            <p id="min"></p>:
            <p id="sec"></p></span>
    </div>
    <span class="handwritten_prop"></span>
    <span class="handwritten_point_to"></span>
    <div class="handwritten_rules">
        <span>领取奖励垂询QQ: 3407150190</span>
    </div>
    {% if users|length > 0 %}
        <span class="handwritten_ranking"></span>
        <div :class="['handwritten_ranking_list',isEnd&&'sm']">
            <div class="handwritten_ranking_list_title">
                <span>昵称</span>
                <span>小黄瓜（个数）</span>
            </div>
            <ul class="handwritten_ranking_ul">
                {% for k, user in users %}
                    <li>
                        {% set index = k + 1 %}
                        {% if index == 1 %}
                            <b class="neo"><i>{{ index }}</i>st</b>
                        {% endif %}
                        {% if index == 2 %}
                            <b class="two"><i>{{ index }}</i>st</b>
                        {% endif %}
                        {% if index == 3 %}
                            <b class="three"><i>{{ index }}</i>st</b>
                        {% endif %}
                        {% if index > 3 %}
                            <b>{{ index }}</b>
                        {% endif %}
                        <span class="name">{{ user.nickname }}</span>
                        <span class="num">{{ user.gift_num }}</span>
                    </li>
                {% endfor %}
            </ul>
        </div>
    {% endif %}
    <span class="handwritten_ranking_leaves"></span>
    <p class="handwritten_ranking_hint">活动最终解释权归Hi语音官方团队</p>
</div>

<script>
    var app = new Vue({
        el: '#handwritten',
        data: {
            isEnd: {{ is_end }}
        },
        methods: {}
    })

    $(function () {
        var end_time = "2018/4/22 00:00";
        countdown(end_time);
    })

    function countdown(end_time) {
        // var EndTime = new Date(that.data.end_time)|| []
        var EndTime = new Date(end_time) || []
        var NowTime = new Date().getTime()
        var total_micro_second = EndTime - NowTime || []


        if (total_micro_second > 0) {
            setTimeout(function () {
                total_micro_second -= 1000;
                countdown(end_time)
            }, 1000)
        } else {
            total_micro_second = 0;
            $('.countdown_box').addClass('over');
        }
        // 总秒数
        var second = Math.floor(total_micro_second / 1000)
        // 天数
        var day = Math.floor(second / 3600 / 24)
        // 小时
        var hr = Math.floor(second / 3600 % 24)
        // 分钟
        var min = Math.floor(second / 60 % 60)
        // 秒
        var sec = Math.floor(second % 60)
        // 时间格式化输出，如11:03 25:19 每1s都会调用一次
        second = toTwo(second)
        day = toTwo(day)
        hr = toTwo(hr)
        min = toTwo(min)
        sec = toTwo(sec)
        // 渲染倒计时时钟
        var dd = document.getElementById("hr");
        var mm = document.getElementById("min");
        var ss = document.getElementById("sec");
        dd.innerText = hr;
        mm.innerText = min;
        ss.innerText = sec;
    }

    /**
     * 封装函数使1位数变2位数
     */
    function toTwo(n) {
        n = n < 10 ? "0" + n : n
        return n
    }
</script>