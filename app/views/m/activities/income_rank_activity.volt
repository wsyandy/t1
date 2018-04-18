{{ block_begin('head') }}
{{ theme_css('/m/css/income_rank_activity') }}
{{ theme_js('/m/rooms/js/font_rem') }}
{{ block_end() }}

<div class="bg_box">
    <div class="top_bg"></div>
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
                <p>活动时间：2018年4月20日0时0分-2018年4月21日0时0分</p>
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