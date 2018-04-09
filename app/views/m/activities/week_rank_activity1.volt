{{ block_begin('head') }}
{{ theme_css('/m/css/week_rank_activity_1') }}
{{ block_end() }}

<div class="voice_box">
    <div class="voice_list">
        <div class="voice_title">活动奖励的靓号</div>
        <table class="table">
            <tr class="title">
                <td></td>
                <td>魅力榜</td>
                <td>
                    <div class="line"></div>
                    贡献榜
                </td>
            </tr>
            <tr>
                <td class="title_size">
                    <img class="voice_ico" src="/m/images/ico_first.png" alt=""> st
                </td>
                <td>靓号:6000000</td>
                <td>靓号:8000000</td>
            </tr>
            <tr>
                <td class="title_size">
                    <img class="voice_ico" src="/m/images/ico_second.png" alt=""> nd
                </td>
                <td>靓号:6000001</td>
                <td>靓号:8000001</td>
            </tr>
            <tr>
                <td class="title_size">
                    <img class="voice_ico" src="/m/images/ico_third.png" alt=""> rd
                </td>
                <td>靓号:6000002</td>
                <td>靓号:8000002</td>
            </tr>
        </table>
    </div>
    <div class="voice_list">
        <div class="voice_title">活动规则</div>
        <ul>
            <li><p>1、</p>
                <p>活动设有魅力榜和贡献榜，用户魅力值越高排名越靠前，贡献值越高排名越靠前。</p></li>
            <li><p>2、</p>
                <p>魅力榜和贡献榜前三名将获得高级靓号奖励。</p></li>
            <li><p>3、</p>
                <p>用户在活动期间送出礼物，每送出1个钻石礼物，送出用户贡献值+1，收到礼物用户魅力值+1。</p></li>
            <li><p>4、</p>
                <p>活动时间：{{ start_text }}-{{ end_text }}</p></li>
        </ul>
    </div>
    <div class="award_text">
        <h3>上周的魅力榜，贡献榜华丽丽的更新啦！</h3>
        <p>更多红人更多福利，持续关注每周Hi榜</p>
    </div>
    <div class="last_week_title">
        <h3>上周Hi音榜</h3>
        <p>{{ last_start_text }}-{{ last_end_text }}</p>
    </div>
    <div class="week_wrap">
        <ul>
            <li class="li_bg">魅力榜</li>
            <li>贡献榜</li>
        </ul>
        <div class="clear"></div>
        <div class="week_list mt_10" style="display: block;">
            <table class="table week_table">
                <tr class="week_tr_title">
                    <td>排名</td>
                    <td>ID</td>
                    <td>昵称</td>
                </tr>
                {% for index,user in charm_users %}
                    <tr>
                        <td class="title_size">
                            {% if index == 0 %}
                                <img class="voice_ico" src="/m/images/ico_first.png" alt=""> st
                            {% elseif index == 1 %}
                                <img class="voice_ico" src="/m/images/ico_second.png" alt=""> nd
                            {% else %}
                                <img class="voice_ico" src="/m/images/ico_third.png" alt=""> rd
                            {% endif %}
                        </td>
                        <td>{{ user.id }}</td>
                        <td>{{ user.nickname }}</td>
                    </tr>
                {% endfor %}
            </table>
        </div>
        <div class="week_list mt_10 none">
            <table class="table week_table">
                <tr class="week_tr_title">
                    <td>排名</td>
                    <td>ID</td>
                    <td>昵称</td>
                </tr>
                {% for index,user in wealth_users %}
                    <tr>
                        <td class="title_size">
                            {% if index == 0 %}
                                <img class="voice_ico" src="/m/images/ico_first.png" alt=""> st
                            {% elseif index == 1 %}
                                <img class="voice_ico" src="/m/images/ico_second.png" alt=""> nd
                            {% else %}
                                <img class="voice_ico" src="/m/images/ico_third.png" alt=""> rd
                            {% endif %}
                        </td>
                        <td>{{ user.id }}</td>
                        <td>{{ user.nickname }}</td>
                    </tr>
                {% endfor %}
            </table>
        </div>
        <!-- 生日结束 -->
    </div>
    {#<div class="last_week_title">#}
    {#<h3>大奖Hi翻天</h3>#}
    {#</div>#}

    {#{% for index,gift in gifts %}#}
    {#<div#}
    {#{% if index == 0 %}#}
    {#class="top_prize one_prize"#}
    {#{% else %}#}
    {#class="top_prize"#}
    {#{% endif %}#}
    {#>#}
    {#<div class="prize_list">#}
    {#<div class="left">#}
    {#<h3>奖励价值 <span>{{ gift.amount }}</span> 钻</h3>#}
    {#<p>{{ gift.name }}</p>#}
    {#</div>#}
    {#<div class="right">#}
    {#<img src="{{ gift.image_big_url }}">#}
    {#</div>#}
    {#</div>#}
    {#</div>#}
    {#{% endfor %}#}

    <div class="active_text">注：请获奖用户联系官方QQ：3407150190及时领取。
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