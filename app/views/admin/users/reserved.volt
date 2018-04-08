<form action="/admin/users/reserved" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="id_eq">ID</label>
    <input name="user[id_eq]" type="text" id="id_eq"/>

    <label for="uid_eq">UID</label>
    <input name="user[uid_eq]" type="text" id="uid_eq"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{% macro user_status_info(user) %}
    {{ user.user_type_text }} | {{ user.user_status_text }}<br/>
    激活时间: {{ user.created_at_text }}<br/>
    注册时间: {{ user.register_at_text }}<br/>
    最后活跃时间: {{ user.last_at_text }}<br/>
    登录方式: {{ user.login_type_text }}<br/>
    用户等级: {{ user.level }}<br/>
    用户所属组织：{{ user.organisation_text }}
{% endmacro %}

{% macro product_channel_view(user) %}
    产品渠道:{{ user.product_channel_name }}<br/>
    FR:{{ user.fr }}<br/>
    FR名称:{{ user.partner_name }}<br/>
    平台:{{ user.platform }} 平台版本:{{ user.platform_version }}<br/>
    版本名称:{{ user.version_name }} 软件版本号:{{ user.version_code }}<br/>
    api协议版本: {{ user.api_version }}<br/>
{% endmacro %}

{{ simple_table(users,['id': 'id','uid': 'uid','头像': 'avatar_image','状态':'user_status_info']) }}

<script type="text/template" id="user_tpl">
    <tr id="user_${user.id}">
        <td>${user.id}</td>
        <td>${user.uid}</td>
        <td><img src="${ user.avatar_small_url }" height="50"/></td>

        <td>
            ${ user.user_type_text } | ${ user.user_status_text }<br/>
            激活时间: ${ user.created_at_text }<br/>
            注册时间: ${ user.register_at_text }<br/>
            最后活跃时间: ${ user.last_at_text }<br/>
            登录方式: ${ user.login_type_text }<br/>
            用户等级: ${ user.level }
        </td>
    </tr>
</script>


<script type="text/javascript">

</script>
