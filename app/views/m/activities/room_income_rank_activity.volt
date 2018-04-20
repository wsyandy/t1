{{ block_begin('head') }}
{{ theme_css('/m/css/income_rank_activity') }}
{{ theme_js('/js/font_rem') }}
{{ block_end() }}

<div class="bg_box "
        {% if rooms %}
    style="height: 68.16rem"
        {% endif %}>
    <div class="top_bg"></div>

    <div class="countdown_box">
        <div class="countdown">
            <p id="hr"> </p>
            <p id="min"> </p>
            <p id="sec"> </p>
        </div>
    </div>

    {% if rooms %}
        <div class="active_box">
            <div class="week_list">
                <table class="table" style="border:0;" cellpadding="0" cellspacing="0">
                    {% for index,room in rooms %}
                        <tr {% if index == 0 %}
                            class="tr_one_title"
                        {% endif %}
                        >
                            <td>
                                {% if index == 0 %}
                                    <img class="voice_ico" src="/m/images/income_rank_activity_one.png" alt="">
                                {% elseif index == 1 %}
                                    <img class="voice_ico" src="/m/images/income_rank_activity_two.png" alt="">
                                {% elseif index == 2 %}
                                    <img class="voice_ico" src="/m/images/income_rank_activity_three.png" alt="">
                                {% else %}
                                    <span>{{ index+1 }}</span>
                                {% endif %}
                            </td>
                            <td>
                            <td>
                                <div class="pic_box">
                                    <div class="pic">
                                        <img src="{{ room.user_avatar_url }}">
                                    </div>
                                    <h3>{{ room.name }}</h3>
                                </div>
                            </td>
                            <td>
                                <h5>{% if index == 0 %}
                                        冠军
                                    {% else %}
                                        距前一名差
                                    {% endif %}
                                </h5>
                            </td>
                            <td>{% if index == 0 %}
                                    <img src="/m/images/guanjun.png" class="guanjun">
                                {% else %}
                                    <b>{{ room.missing_income }}</b>
                                {% endif %}
                            </td>
                        </tr>
                    {% endfor %}
                </table>
            </div>
        </div>
    {% endif %}

    <div class="active_gz">
        <ul>
            <li>
                <p>1.</p>
                <p>其房间流水排行第一名的房主用户将获得靓号 <span>556677</span> 与 <span>20000</span> 元现金奖励；</p>
            </li>
            <li>
                <p>2.</p>
                <p>排行榜按活动期间房间流水（房间内打赏的钻石总数）累计值实时更新</p>
            </li>
            <li>
                <p>3.</p>
                <p>活动时间：2018年4月20日0时-2018年4月20日24时</p>
            </li>
            <li>
                <p>4.</p>
                <p>获奖用户联系官方QQ：3407150190领取奖励；</p>
            </li>
        </ul>
    </div>
    <div class="award_text">
        活动最终解释权归Hi语音官方团队
    </div>
</div>

<script>
    $(function () {
        $(".week_wrap ul li").each(function (i) {
            $(this).click(function () {
                $(this).addClass("li_bg").siblings().removeClass("li_bg");
                $(".week_list:eq(" + i + ")").show().siblings(".week_list").hide();
            })
        })

        var end_time = "2018/4/21 00:00";

        countdown(end_time)
    })


    function countdown(end_time) {
        // var EndTime = new Date(that.data.end_time)|| []
        var EndTime = new Date(end_time) || []
        var NowTime = new Date().getTime()
        var total_micro_second = EndTime - NowTime || []



        if(total_micro_second>0){
            setTimeout(function () {
                total_micro_second -= 1000;
                countdown( end_time)
            }, 1000)
        }else {
            total_micro_second= 0;
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
        var dd=document.getElementById("hr");
        var mm=document.getElementById("min");
        var ss=document.getElementById("sec");
        dd.innerText=hr;
        mm.innerText=min;
        ss.innerText=sec;
    }
    /**
     * 封装函数使1位数变2位数
     */
    function toTwo(n) {
        n = n < 10 ? "0" + n : n
        return n
    }

</script>