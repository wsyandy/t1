{{ block_begin('head') }}
{{ theme_css('/m/css/activity_index') }}
{{ block_end() }}

<div class="activity_page">
    <ul class="activity_ul">
        {% for activity in activities %}
            <li>
                <img src="{{ activity.image_url }}" alt="">
                <div class=" activity_content">
                    <p>Hi语音周榜争夺战启动中…</p>
                    <div class="activity_content_bottom">
                        <span>{{ activity.start_text }}-{{ activity.end_text }}</span>
                        <span class="arrow" id="{{ activity.id }}"> 了解详情</span>
                    </div>
                </div>
            </li>
        {% endfor %}
    </ul>
</div>

<script>
    $('.activity_page ul li').each(function () {
        $(this).click(function () {
            $(this).addClass('time_min_selected').siblings().removeClass('time_min_selected');
            var id = $(this).find('.arrow').attr("id");
            location.href = "/m/activities/week_rank_activity?id=" + id + "&code=" + '{{ code }}';
        })
    });

</script>