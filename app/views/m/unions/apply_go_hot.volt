{{ block_begin('head') }}
{{ theme_css('/m/css/union_main','/m/css/hot') }}
{{ block_end() }}

<div class="hot_box">
    <ul>
        <li>
            <span>用户ID</span>
            <input type="text" id="user_id" placeholder="用户ID">
        </li>
        <li>
            <span>直播简介</span>
            <input type="text" id="introduce" placeholder="5-50个字" maxlength="50" minlength="5">
        </li>
    </ul>
</div>
<div class="hot_box">
    <ul>
        <li>
            <span>时间选取</span>
            <b class="select_time" id="select_time"></b>
        </li>
    </ul>
</div>
<div class="hot_btn">
    <span>申请上热门</span>
</div>
<div class="hot_time_box_bg"></div>
<div class="hot_time_box">
    <h3>时间选取</h3>
    <div class="time_list">
        <div class="time_day">
            <ol>
                {% for key,value in days %}
                    <li id="{{ value }}">{{ key }}</li>
                {% endfor %}
            </ol>
        </div>
        <div class="time_min">
            <ul>
                {% for key,value in hours %}
                    <li>
                        <span id="{{ value }}">{{ key }}</span>
                        <i></i>
                    </li>
                {% endfor %}
            </ul>
        </div>
    </div>
</div>

<script>

    var day = '';
    var hour = '';
    var hour_html = '';
    var day_html = '';
    $(function () {
        //设置日期默认选项
        var first_li_1 = $("ol li:eq(0)");
        first_li_1.addClass('day_selected');
        first_li_1.html(first_li_1.html() + "（明天）");
        day = first_li_1.attr("id");
        day_html = first_li_1.html();

        var first_li_2 = $("ol li:eq(1)");
        first_li_2.html(first_li_2.html() + "（后天）");

        //设置时间段默认选项
        var selected_ul_li = $(".time_min ul li:eq(4)");
        selected_ul_li.addClass('time_min_selected');
        hour = selected_ul_li.find('span').attr("id");
        hour_html = selected_ul_li.find('span').html();

        var select_time = $(".select_time");
        select_time.html(day_html + hour_html);

        select_time.click(function () {
            $('.hot_time_box').show();
            $('.hot_time_box_bg').show();
        });

        $('.time_min_selected').click(function () {
            $(this).toggleClass('');
        });

        $('.time_day ol li').each(function () {
            $(this).click(function () {
                day = $(this).attr("id");
                day_html = $(this).html();
                $(this).addClass('day_selected').siblings().removeClass('day_selected');
            })
        });

        $('.time_min ul li').each(function () {
            $(this).click(function () {
                $(this).addClass('time_min_selected').siblings().removeClass('time_min_selected');
                hour_html = $(this).find('span').html();
                hour = $(this).find('span').attr("id");
                $(".select_time").html(day_html + hour_html);
                $('.hot_time_box').hide();
                $('.hot_time_box_bg').hide();
                $('.hot_btn').addClass('hot_btn_selected');
            })
        });


        $('.hot_btn').click(function () {
            create();
        });

        function create() {
            console.log(day);
            console.log(hour);
            var introduce = $('#introduce').val();
            var user_id = $('#user_id').val();

            var data = {
                sid: "{{ sid }}",
                code: "{{ code }}",
                day: day,
                hour: hour,
                introduce: introduce,
                user_id: user_id
            };

            $.authPost("/m/unions/hot_room_history", data, function (resp) {
                alert(resp.error_reason);
                if (resp.error_code == 0 && resp.error_url) {
                    location.href = "/m/unions/my_union?sid={{ sid }}&code={{ code }}";
                }
            })
        }
    })
</script>

