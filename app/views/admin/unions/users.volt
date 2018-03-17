{% if isAllowed('unions','add_user') %}
    <a href="/admin/unions/add_user?id={{ id }}" class="modal_action">新建</a>
{% endif %}

{% macro avatar_image(user) %}
    <img src="{{ user.avatar_small_url }}" height="50"/>
{% endmacro %}

{% macro user_info(user) %}
    手机号码:{{ user.mobile }}<br/>
    经纬度定位: {{ user.geo_province_name }}, {{ user.geo_city_name }}<br/>
    IP定位: {{ user.ip_province_name }}, {{ user.ip_city_name }}<br/>
    自述城市信息: {{ user.province_name }}, {{ user.city_name }}
{% endmacro %}

{% macro user_status_info(user) %}
    {{ user.user_type_text }} | {{ user.user_status_text }}<br/>
    用户等级: {{ user.level }}
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
    {% if isAllowed('rooms','index') %}
        <a href="/admin/rooms?room[id_eq]={{ user.room_id }}">房间</a>
        {% if user.current_room_id %}
            <a href="/admin/rooms?room[id_eq]={{ user.current_room_id }}">所在房间</a>
        {% endif %}
        <br/>
    {% endif %}
    {% if isAllowed('unions','delete_user') %}
        <a href="/admin/unions/delete_user?user_id={{ user.id }}&id={{ id }}" id="delete_user">删除</a>
    {% endif %}
{% endmacro %}

{{ simple_table(users,['用户id': 'id','头像': 'avatar_image', '用户信息':'user_info',
'状态':'user_status_info', '操作':'profile_link'
]) }}

<script type="text/template" id="user_tpl">
    <tr id="user_${user.id}">
        <td>${user.id}</td>
        <td><img src="${ user.avatar_small_url }" height="50"/></td>
        <td>
            姓名:${ user.nickname } 性别:${ user.sex_text }<br/>
            手机号码:${ user.mobile }<br/>
            经纬度定位: ${ user.geo_province_name }, ${ user.geo_city_name }<br/>
            IP定位: ${ user.ip_province_name }, ${ user.ip_city_name }<br/>
        </td>
        <td>
            ${ user.user_type_text } | ${ user.user_status_text }<br/>
            激活时间: ${ user.created_at_text }<br/>
            用户等级: ${ user.level }
        </td>
        <td>
            {% if isAllowed('users','detail') %}
                <a href="/admin/users/detail?id=${ user.id }">详情</a><br/>
            {% endif %}
            {% if isAllowed('rooms','index') %}
                <a href="/admin/rooms?room[id_eq]=${ user.room_id }">房间</a>
                {@if user.current_room_id }
                <a href="/admin/rooms?room[id_eq]=${ user.current_room_id }">所在房间</a>
                {@/if}
                <br/>
            {% endif %}
        </td>

    </tr>
</script>


<script type="text/javascript">
    $('body').on('click', '#delete_user', function (e) {
        e.preventDefault();
        if (confirm('确认删除？')) {
            var href = $(this).attr('href');
            $.post(href, '', function (resp) {
                alert(resp.error_reason);
            });
        }
    });
</script>