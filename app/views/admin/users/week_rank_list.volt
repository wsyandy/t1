<form method="get" action="/admin/users/week_rank_list" name="search_form" autocomplete="off">
    <label for="product_channel_id">产品渠道</label>
    <select name="product_channel_id" id="product_channel_id">
        {{ options(product_channels,product_channel_id,'id','name') }}
    </select>

    <label for="stat_at">时间</label>
    <input type="text" name="stat_at" class="form_datetime" id="stat_at" value="{{ stat_at }}" size="16">

    <label for="year">榜单类型</label>
    <select name="type" id="type">
        {{ options(types,type) }}
    </select>

    <button class="ui button" type="submit">搜索</button>
</form>

{% macro avatar_image(user) %}
    <img src="{{ user.avatar_small_url }}" height="50"/>
{% endmacro %}

{% macro user_info(user) %}
    姓名:{{ user.nickname }}  性别:{{ user.sex_text }} 段位:{{ user.segment_text }}<br/>
    {% if user.charm %}
        魅力值:{{ user.charm }}<br/>
    {% endif %}
    {% if user.wealth %}
        财富值:{{ user.wealth }}<br/>
    {% endif %}
{% endmacro %}

{{ simple_table(users,['用户id': 'id','头像': 'avatar_image', '用户信息':'user_info']) }}

<script type="text/javascript">


    $(function () {
        $(".form_datetime").datetimepicker({
            language: "zh-CN",
            format: 'yyyy-mm-dd',
            weekStart: 1,
            autoclose: 1,
            todayBtn: 1,
            todayHighlight: 1,
            startView: 2,
            minView: 2,
            daysOfWeekDisabled: [0, 2, 3, 4, 5, 6]
        });
    });
</script>