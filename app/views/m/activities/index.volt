{{ block_begin('head') }}
{{ theme_css('/m/css/activity_index') }}
{{ block_end() }}

<div class="activity_page">
    <ul class="activity_ul">
        {% for activity in activities %}
            <li data-id="{{ activity.id }}" {% if activity.isForbidden() or activity.isOver() %} data-status="-1" style="background-color:grey"{% endif %}>
                <img src="{{ activity.image_url }}" alt="">
                <div class=" activity_content">
                    <p>{{ activity.title }}</p>
                    <div class="activity_content_bottom">
                       <span>
                        {% if activity.isOver() %}
                            已结束
                        {% elseif activity.start_at %}
                            {{ activity.start_text }}-{{ activity.end_text }}
                        {% endif %}
                       </span>
                        <span class="arrow" id="{{ activity.id }}"> 了解详情</span>
                        <input type="hidden" id="code" value="{{ activity.code }}">
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
            var code = $(this).find('#code').attr("value");

            if ('gold_eggs_draw' == code) {
                window.location.href = "/m/draw_histories?sid=" + '{{ sid }}' + "&code=" + '{{ code }}';
                return;
            }

            if (code) {
                window.location.href = "/m/activities/" + code + "?id=" + id + "&sid=" + '{{ sid }}' + "&code=" + '{{ code }}';
            } else {
                window.location.href = "/m/activities/week_rank_activity?id=" + id + "&sid=" + '{{ sid }}' + "&code=" + '{{ code }}';
            }
        })
    });

</script>