{{ block_begin('head') }}
{{ theme_css('/m/css/qing_ming_activity') }}
{{ theme_js('/m/js/font_rem') }}
{{ block_end() }}

<div class="active_top">
    <div class="top_text">
        <p>春天到了，四月的清明节如期而至，然而没有雨打芭蕉的忧愁，春日的气息带来的是内心说不清的悸动。</p>
        <p>这个软萌的日子里，</p>
        <p>小Hi给大家准备的清明礼物——<span>青团</span>，</p>
        <p>希望大家喜欢~不仅如此，小Hi还给男神女神带来更大的惊喜！</p>
    </div>
</div>
<div class="active_main">
    <img src="/m/images/left.png" class="left">
    <img src="/m/images/right.png" class="right">
    <div class="active_jl">
        <div class="active_title">
            <h3>青团活动奖励</h3>
            <p></p>
        </div>
        <ul>
            <li>
                <h4>第一名</h4>
                <img src="/m/images/01.png">
                <p>新礼物<br>一周冠名权</p>
            </li>
            <li>
                <h4>第二名</h4>
                <img src="/m/images/02.png">
                <p>神秘座驾</p>
            </li>
            <li>
                <h4>第三名</h4>
                <img src="/m/images/03.png">
                <p>20000金币</p>
            </li>
        </ul>
    </div>
    <div class="active_gz">
        <div class="active_title">
            <h3>活动规则</h3>
            <p class="line"></p>
        </div>
        <ul>
            <li>
                <p>1.</p>
                <p>清明节活动期间，送出礼物青团最多的前三甲以及收到青团最多的前三甲将获得榜单对应奖励。</p>
            </li>
            <li>
                <p>2.</p>
                <p>活动期间，用户每送出一个青团。贡献值＋20，每收到一个青团，魅力值＋20。</p>
            </li>
            <li>
                <p>3.</p>
                <p>当两个用户送出或获得青团数量相同时，先达到的用户排名更靠前。</p>
            </li>
            <li>
                <p>4.</p>
                <p>活动时间：{{ start_text }}—{{ end_text }}</p>
            </li>
        </ul>
    </div>
</div>
<div class="active_box">
    <div class="active_book"></div>
    <div class="week_wrap">
        <ul>
            <h1>青团实时榜单</h1>
            <li class="li_bg">魅力榜</li>
            <li>贡献榜</li>
        </ul>
        <div class="clear"></div>
        <div class="week_list" style="display: block;"><img src="/m/images/qing_ming_activity_down.png" class="down">
            <table class="table">
                <tr class="week_tr_title">
                    <td>排名</td>
                    <td style="text-align:left;width:40%;">昵称／ID</td>
                    <td>魅力值</td>
                </tr>
                {% for index,user in charm_users %}
                    <tr>
                        <td class="title_size">
                            {% if index == 0 %}
                                <img class="voice_ico" src="/m/images/one.png" alt="">
                            {% elseif index == 1 %}
                                <img class="voice_ico" src="/m/images/two.png" alt="">
                            {% elseif index == 2 %}
                                <img class="voice_ico" src="/m/images/three.png" alt="">
                            {% else %}
                                {{ index+1 }}
                            {% endif %}
                        </td>
                        <td>
                            <h5>{{ user.nickname }}</h5>
                            <p>{{ user.id }}</p>
                        </td>
                        <td><b>{{ user.value }}</b></td>
                    </tr>
                {% endfor %}
            </table>
        </div>
        <div class="week_list none">
            <img src="/m/images/qing_ming_activity_down.png" class="down down_right">
            <table class="table week_table">
                <tr class="week_tr_title">
                    <td>排名</td>
                    <td style="text-align:left;width:40%;">昵称／ID</td>
                    <td>魅力值</td>
                </tr>

                {% for index,user in wealth_users %}
                    <tr>
                        <td class="title_size">
                            {% if index == 0 %}
                                <img class="voice_ico" src="/m/images/one.png" alt="">
                            {% elseif index == 1 %}
                                <img class="voice_ico" src="/m/images/two.png" alt="">
                            {% elseif index == 2 %}
                                <img class="voice_ico" src="/m/images/three.png" alt="">
                            {% else %}
                                {{ index+1 }}
                            {% endif %}
                        </td>
                        <td>
                            <h5>{{ user.nickname }}</h5>
                            <p>{{ user.id }}</p>
                        </td>
                        <td><b>{{ user.value }}</b></td>
                    </tr>
                {% endfor %}
            </table>
        </div>
    </div>
</div>
<div class="award_text">
    活动最终解释权归Hi语音官方团队
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