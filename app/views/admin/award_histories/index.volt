<form action="/admin/award_histories" method="get" class="search_form" autocomplete="off" id="search_form">

    <label for="stat_at_eq">时间</label>
    <input name="stat_at" type="text" id="stat_at_eq" class="form_datetime" value="{{ stat_at }}"/>

    <label for="id_eq">ID</label>
    <input name="award_history[id_eq]" type="text" id="id_eq"/>

    <label for="user_id_eq">用户ID</label>
    <input name="award_history[user_id_eq]" type="text" id="user_id_eq"/>

    <label for="status">状态</label>
    <select name="award_history[status_eq]" id="status_eq">
        {{ options(AwardHistories.STATUS) }}
    </select>

    <label for="auth_status">审核状态</label>
    <select name="award_history[auth_status_eq]" id="auth_status_eq">
        {{ options(AwardHistories.AUTH_STATUS) }}
    </select>

    <button type="submit" class="ui button">搜索</button>
</form>
{% macro edit_link(award_history) %}
    {% if award_history.auth_status == 3 %}
        <a class="modal_action"
           href="/admin/award_histories/send_system_message?id={{ award_history.id }}&user_id={{ award_history.user_id }}">审核</a>
    {% else %}
        <a href="#">已审核</a>
    {% endif %}
{% endmacro %}

{{ simple_table(award_histories,['id': 'id','用户名':'user_nickname','奖励类型':'type_text','金额':'amount','状态':'status_text','审核状态':'auth_status_text','操作':'edit_link']) }}


<script type="text/javascript">
    $(".form_datetime").datetimepicker({
        language: "zh-CN",
        format: 'yyyy-mm',
        autoclose: 1,
        todayBtn: 1,
        todayHighlight: 1,
        startView: 2,
        minView: 2
    });
</script>
