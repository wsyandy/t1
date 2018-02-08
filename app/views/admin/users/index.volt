<form action="/admin/users" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="user[product_channel_id_eq]" id="product_channel_id_eq">
        {{ options(product_channels,'','id','name') }}
    </select>

    <label for="id_eq">ID</label>
    <input name="user[id_eq]" type="text" id="id_eq"/>

    <label for="mobile">手机号</label>
    <input name="user[mobile_eq]" type="text" id="mobile"/>

    <label for="user_type">类型</label>
    <select name="user[user_type_eq]" id="user_type_eq">
        {{ options(user_types, '') }}
    </select>

    <button type="submit" class="ui button">搜索</button>
</form>

{% macro avatar_image(user) %}
    <img src="{{ user.avatar_small_url }}" height="50"/>
{% endmacro %}

{% macro user_info(user) %}
    姓名:{{ user.nickname }}  性别:{{ user.sex_text }}<br/>
    手机号码:{{ user.mobile }}<br/>
    设备ID:<a href="/admin/devices?device[id_eq]={{ user.device_id }}">{{ user.device_id }}</a><br/>
    经纬度定位: {{ user.geo_province_name }}, {{ user.geo_city_name }}<br/>
    IP定位: {{ user.ip_province_name }}, {{ user.ip_city_name }}<br/>
    自述城市信息: {{ user.province_name }}, {{ user.city_name }}
{% endmacro %}

{% macro user_status_info(user) %}
    {{ user.user_type_text }} | {{ user.user_status_text }}<br/>
    激活时间: {{ user.created_at_text }}<br/>
    注册时间: {{ user.register_at_text }}<br/>
    最后活跃时间: {{ user.last_at_text }}<br/>
{% endmacro %}

{% macro product_channel_view(user) %}
    产品渠道:{{ user.product_channel_name }}<br/>
    FR:{{ user.fr }}<br/>
    FR名称:{{ user.partner_name }}<br/>
    平台:{{ user.platform }}<br/>
    api协议版本: {{ user.api_version }}<br/>
    客户端版本: {{ user.version_code }}<br/>
{% endmacro %}

{% macro profile_link(user) %}
    {% if isAllowed('users','detail') %}
        <a href="/admin/users/detail?id={{ user.id }}">详情</a><br/>
    {% endif %}
    {% if isAllowed('users','edit') %}
        <a class="modal_action" href="/admin/users/edit?id={{ user.id }}">编辑</a><br/>
    {% endif %}
    {% if isAllowed('rooms','index') %}
        <a href="/admin/rooms?room[id_eq]={{ user.room_id }}">房间</a><br/>
    {% endif %}
    {% if isAllowed('users','send_message') %}
        <a href="/admin/users/send_message?id={{ user.id }}" class="modal_action">发送系统消息</a><br/>
    {% endif %}
    {% if isAllowed('users','getui') %}
        <a href="/admin/users/getui?receiver_id={{ user.id }}" class="modal_action">发送个推消息</a><br/>
    {% endif %}
{% endmacro %}

{{ simple_table(users,['用户id': 'id','头像': 'avatar_image', '渠道信息:':'product_channel_view', '用户信息':'user_info',
    '状态':'user_status_info', '操作':'profile_link'
]) }}

<script type="text/template" id="user_tpl">
    <tr id="user_${user.id}">
        <td>${user.id}</td>
        <td><img src="${ user.avatar_small_url }" height="50"/></td>
        <td>
            产品渠道:${ user.product_channel_name }<br/>
            FR:${ user.fr }<br/>
            FR名称:${ user.partner_name }<br/>
            平台:${ user.platform }<br/>
            api协议版本: ${ user.api_version }<br/>
            客户端版本: ${user.version_code}<br/>
        </td>
        <td>
            姓名:${ user.id_name } 性别:${ user.sex_text }<br/>
            手机号码:${ user.mobile }<br/>
            设备ID:<a href="/admin/devices?device[id_eq]=${user.device_id}">${user.device_id}</a><br/>
            经纬度定位: ${ user.geo_province_name }, ${ user.geo_city_name }<br/>
            IP定位: ${ user.ip_province_name }, ${ user.ip_city_name }<br/>
            自述城市信息: ${ user.province_name }, ${ user.city_name }
        </td>
        <td>
            ${ user.user_type_text } | ${ user.user_status_text }<br>
            激活时间: ${ user.created_at_text }<br/>
            注册时间: ${ user.register_at_text }<br/>
            最后活跃时间: ${ user.last_at_text }<br/>
        </td>
        <td>
            <a href="/admin/users/detail?id=${ user.id }">详情</a><br/>
            <a href="/admin/users/edit/${user.id}" class="modal_action">编辑</a><br/>
            <a href="/admin/rooms?room[id_eq]=${ user.room_id }">房间</a><br/>
            <a href="/admin/users/send_message?id=${ user.id }" class="modal_action">发送系统消息</a><br/>
            <a href="/admin/users/getui?receiver_id=${ user.id }" class="modal_action">发送个推消息</a><br/>
        </td>

    </tr>
</script>


<script type="text/javascript">
    $('body').on('click', '#add_friends', function (e) {
        e.preventDefault();
        if (confirm('确认添加？')) {
            var href = $(this).attr('href');
            $.post(href, '', function (resp) {
                alert(resp.error_reason);
            });
        }
    });

    $('body').on('click', '#follow', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        if (confirm('确认关注')) {
            $.post(href, '', function (resp) {
                alert(resp.error_reason);
            })
        }
    })
</script>
