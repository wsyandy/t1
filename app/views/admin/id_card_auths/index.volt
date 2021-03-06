<form action="/admin/id_card_auths" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="id_card_auth[product_channel_id_eq]" id="product_channel_id_eq">
        {{ options(product_channels,'','id','name') }}
    </select>

    <label for="auth_status_eq">审核状态</label>
    <select name="id_card_auth[auth_status_eq]" id="auth_status_eq">
        {{ options(IdCardAuths.AUTH_STATUS, auth_status) }}
    </select>

    <label for="id_eq">ID</label>
    <input name="id_card_auth[id_eq]" value="{{ id }}" type="text" id="id_eq"/>

    <label for="user_id_eq">用户id</label>
    <input name="id_card_auth[user_id_eq]" value="{{ user_id }}" type="text" id="user_id_eq"/>
    <button type="submit" class="ui button">搜索</button>
</form>

<ol class="breadcrumb">
    <li class="active">认证成功的人数数:{{ auth_success_num }} </li>
    <li class="active">等待认证的人数数:{{ auth_wait_num }} </li>
    <li class="active">认证失败的人数数:{{ auth_fail_num }} </li>
</ol>

{% macro user_info(id_card_auth) %}
    {% if isAllowed('users','index') %}
        姓名:<a href="/admin/users?user[id_eq]={{ id_card_auth.user_id }}">{{ id_card_auth.user_nickname }}</a><br/>
    {% endif %}
    性别:{{ id_card_auth.user.sex_text }}<br/>
    实名认证状态:{{ id_card_auth.user.id_card_auth_text }}<br/>
{% endmacro %}

{% macro account_link(id_card_auth) %}
    姓名:{{ id_card_auth.id_name }}<br/>
    身份证:{{ id_card_auth.id_no }}<br/>
    手机号码:{{ id_card_auth.mobile }}<br/>
{% endmacro %}


{% macro operate_link(id_card_auth) %}
    {% if isAllowed('id_card_auths','edit') %}
        <a href="/admin/id_card_auths/edit?id={{ id_card_auth.id }}" class="modal_action">编辑</a></br>
    {% endif %}
{% endmacro %}

{% macro avatar_image(id_card_auth) %}
    <img src="{{ id_card_auth.user_avatar_url }}" height="50" width="50"/>
{% endmacro %}

{{ simple_table(id_card_auths,['id': 'id','头像':'avatar_image','审核时间':'auth_at_text','用户信息':"user_info",'认证信息':'account_link','审核状态':'auth_status_text',"操作":"operate_link"]) }}

<script>
    {% for id_card_auth in id_card_auths %}
    {% if id_card_auth.auth_status == 2 %}
    $("#id_card_auth_{{ id_card_auth.id }}").css({"background-color": "grey"});
    {% endif %}
    {% endfor %}

</script>