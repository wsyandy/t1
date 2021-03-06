<form action="/admin/unions/family" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="id">ID</label>
    <input name="id" type="number" id="id">
    <label for="uid">UID</label>
    <input name="uid" type="number" id="uid">

    <label for="user_id">用户ID</label>
    <input name="user_id" type="number" id="user_id">

    <label for="status">状态</label>
    <select name="status" type="text" id="status">
        {{ options(Unions.STATUS,status,'') }}
    </select>

    <label for="auth_status">审核状态</label>
    <select name="auth_status" type="text" id="auth_status">
        {{ options(Unions.AUTH_STATUS, auth_status,'') }}
    </select>

    <button type="submit" class="ui button">搜索</button>
</form>

{% macro oper_link(union) %}
    {% if isAllowed('users','index') %}
        <a href="/admin/users/index?user[union_id_eq]={{ union.id }}">家族成员</a><br/>
    {% endif %}
    {% if isAllowed('unions','update_permissions') %}
        <a href="/admin/unions/update_permissions/{{ union.id }}" class="modal_action">家族权限</a><br/>
    {% endif %}
    {#{% if isAllowed('unions','update_room_ids') %}#}
        {#<a href="/admin/unions/update_room_ids/{{ union.id }}" class="modal_action">查看房间权限</a><br/>#}
    {#{% endif %}#}
    {% if isAllowed('unions','auth') and union.auth_status == 3 %}
        <a href="/admin/unions/auth/{{ union.id }}" class="modal_action">审核</a><br/>
    {% endif %}
    {% if isAllowed('unions','edit') %}
        <a href="/admin/unions/edit/{{ union.id }}" class="modal_action">编辑</a><br/>
    {% endif %}
    {% if isAllowed('unions','update_integrals') %}
        <a href="/admin/unions/update_integrals?id={{ union.id }}" class="modal_action">家族积分</a><br/>
    {% endif %}
{% endmacro %}

{% macro avatar_img(union) %}
    <img src="{{ union.avatar_small_url }}" height="50"/>
{% endmacro %}

{% macro user_link(union) %}
    {% if isAllowed('users','index') %}
        <a href="/admin/users/index?user[id_eq]={{ union.user_id }}">{{ union.user_nickname }}</a><br/>
    {% endif %}
{% endmacro %}

{% macro family_info(union) %}
    声望：{{ union.fame_value }} 成员数：{{ union.user_num }}<br/>
    推荐：{{ union.recommend_text }} 设置：{{ union.need_apply_text }} <br/>
    公告：{{ union.notice }}<br/>
    家族等级：{{ union.union_level }}<br/>
    总积分：{{ union.total_integrals }}<br/>
{% endmacro %}

{{ simple_table(unions, ['ID': 'id','uid': 'uid',"头像":"avatar_img",'家族名称': 'name','族长': 'user_link','家族信息':'family_info',
    '创建时间':'created_at_text','解散时间':'dismissed_at_text',
    '创建家族花费钻石数额':'create_union_cost_amount','状态': 'status_text','操作' :'oper_link'
]) }}