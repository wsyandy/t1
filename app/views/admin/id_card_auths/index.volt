<form action="/admin/id_card_auths" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="id_card_auth[product_channel_id_eq]" id="product_channel_id_eq">
        {{ options(product_channels,'','id','name') }}
    </select>

    <label for="auth_status_eq">审核状态</label>
    <select name="id_card_auth[auth_status_eq]" id="auth_status_eq">
        {{ options(IdCardAuths.AUTH_STATUS) }}
    </select>

    <label for="id_eq">ID</label>
    <input name="id_card_auth[id_eq]" type="text" id="id_eq"/>

    <label for="user_id_eq">用户id</label>
    <input name="id_card_auth[user_id_eq]" type="text" id="user_id_eq"/>
    <button type="submit" class="ui button">搜索</button>
</form>


{% macro user_info(id_card_auth) %}
    {% if isAllowed('users','index') %}
        姓名:<a href="/admin/users?user[id_eq]={{ id_card_auth.user_id }}">{{ id_card_auth.user_nickname }}</a><br/>
    {% endif %}
    性别:{{ id_card_auth.user.sex_text }}<br/>
    主播认证状态:{{ id_card_auth.user.id_card_auth_text }}<br/>
{% endmacro %}


{% macro operate_link(id_card_auth) %}
    {% if isAllowed('id_card_auth','edit') %}
        <a href="/admin/id_card_auths/edit?id={{ id_card_auth.id }}" class="modal_action">编辑</a></br>
    {% endif %}
{% endmacro %}

{% macro avatar_image(id_card_auth) %}
    <img src="{{ id_card_auth.user_avatar_url }}" height="50" width="50"/>
{% endmacro %}

{{ simple_table(id_card_auths,['id': 'id','头像':'avatar_image','用户信息':"user_info",'审核状态':'auth_status_text',"操作":"operate_link"]) }}