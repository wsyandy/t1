{{ block_begin('head') }}
{{ theme_css('/m/css/income_rank_activity') }}
{{ theme_js('/js/font_rem') }}
{{ block_end() }}

<div class="bg_box "
        {% if rooms %}
    style="height: 68.16rem"
        {% endif %}>
    <div class="top_bg"></div>

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
                                    <span>{{ index }}</span>
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
    })
</script>