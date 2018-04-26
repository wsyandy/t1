{{ block_begin('head') }}
{{ theme_css('/m/css/income_rank_activity6') }}
{{ theme_js('/m/js/income_rank_activity_1') }}
{{ block_end() }}

<div id="app" class="water_activity" v-cloak="">
    <div v-if="activityState>=1" class="water_activity_start"></div>
    <div v-if="activityState<2" :class="['water_activity_trailer',activityState==1&&'start_countdown']">
        <p id="time"></p>
    </div>
    <div v-if="activityState==0" class="water_activity_title">
        <img src="/m/images/week_activity_title_bg.png" alt="文字说明"/>
        <span>(价值5万元)</span>
    </div>
    <div v-if="activityState>=1" :style="{marginTop: '.5rem'}" class="water_activity_list_title"></div>
    {% if rooms %}
        <div v-if="activityState>=1" class="water_activity_list">
            <p class="water_activity_list_top"><span>排名</span><span>房主昵称</span></p>
            <ul class="water_activity_ul">
                {% for index,room in rooms %}
                    <li>
                        {% if index == 0 %}
                            <span class="ranking_neo"></span>
                        {% elseif index == 1 %}
                            <span class="ranking_two"></span>
                        {% elseif index == 2 %}
                            <span class="ranking_three"></span>
                        {% else %}
                            <span class="ranking">{{ index +1 }}</span>
                        {% endif %}
                        <img class="header" src="{{ room.user_avatar_url }}" alt="头像"/>
                        <span class="name">{{ room.user_nickname }}</span>
                        {% if index == 0 %}
                            <span class="prompt">冠军</span>
                            <span class="trophy"></span>
                        {% else %}
                            <span class="prompt">距前一名差</span>
                            <span class="score">{{ room.missing_income }}</span>
                        {% endif %}
                    </li>
                {% endfor %}
            </ul>
        </div>
    {% endif %}
    <div class="water_activity_rules">
        <span class="title">活动规则</span>
        <div class="water_activity_rules_line">
            <span class="dot"></span>
            <p>其房间流水排行第一名的房主用户将获得靓号 <i>666777</i> 和 <i>500000</i> 钻奖励；</p>
        </div>
        <div class="water_activity_rules_line">
            <span class="dot"></span>
            <p>排行榜按活动期间房间流水（房间内打赏的钻石总数）累计值实时更新；</p>
        </div>
        <div class="water_activity_rules_line">
            <span class="dot"></span>
            <p>活动时间：{{ start }}—{{ end }}</p>
        </div>
        <div class="water_activity_rules_line">
            <span class="dot"></span>
            <p>获奖用户联系官方QQ：3407150190领取奖励；</p>
        </div>
    </div>
    <p class="water_activity_copyright">活动最终解释权归Hi语音官方团队</p>
</div>

<script>
    var opts = {
        data: {
            //活动状态:0预告、1进行、2结束
            activityState: {{ activity_state }}
        },
        methods: {}
    };

    vm = XVue(opts);

    $(function () {

        var start_time = '{{ start_time }}';
        var end_time = '{{ end_time }}';

        countdown(end_time, start_time)
    });


    function countdown(end_time, start_time) {
        // var EndTime = new Date(that.data.end_time)|| []
        var StartTime = new Date(start_time) || [];
        var EndTime = new Date(end_time) || [];
        var NowTime = new Date().getTime();

        if (NowTime >= EndTime) {
            if (vm.activityState != 2) {
                location.reload();
            }
        } else if (NowTime >= StartTime) {
            if (vm.activityState != 1) {
                location.reload();
            }
        }


        if (NowTime < StartTime) {
            EndTime = StartTime;
        }

        var total_micro_second = EndTime - NowTime || [];

        console.log(total_micro_second);

        if (total_micro_second > 0) {
            setTimeout(function () {
                total_micro_second -= 1000;
                countdown(end_time, start_time)
            }, 1000)
        } else {
            total_micro_second = 0;
            return;
        }
        // 总秒数
        var second = Math.floor(total_micro_second / 1000);
        // 天数
        var day = Math.floor(second / 3600 / 24);
        // 小时
        var hr = Math.floor(second / 3600 % 24);
        // 分钟
        var min = Math.floor(second / 60 % 60);
        // 秒
        var sec = Math.floor(second % 60);
        // 时间格式化输出，如11:03 25:19 每1s都会调用一次
        day = toTwo(day);
        hr = toTwo(hr);
        min = toTwo(min);
        sec = toTwo(sec);
        // 渲染倒计时时钟

        var time = parseInt(day) + "天" + parseInt(hr) + ":" + parseInt(min) + ":" + parseInt(sec);

        var _time = document.getElementById("time");

        _time.innerText = time;
    }
    /**
     * 封装函数使1位数变2位数
     */
    function toTwo(n) {
        n = n < 10 ? "0" + n : n;
        return n
    }
</script>
