<form action="/admin/users" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="user[product_channel_id_eq]" id="product_channel_id_eq">
        {{ options(product_channels,product_channel_id,'id','name') }}
    </select>

    <label for="id_eq">ID</label>
    <input name="user[id_eq]" type="text" id="id_eq" value="{{ user_id }}"/>

    <label for="uid_eq">UID</label>
    <input name="user[uid_eq]" type="text" id="uid_eq"/>

    <label for="mobile">手机号</label>
    <input name="user[mobile_eq]" type="text" id="mobile" value="{{ mobile }}"/>

    <label for="nickname">用户昵称</label>
    <input name="nickname" type="text" id="nickname" value="{{ nickname }}"/>

    <label for="user_type">类型</label>
    <select name="user[user_type_eq]" id="user_type_eq">
        {{ options(user_types, user_type) }}
    </select>

    <label for="user_status">状态</label>
    <select name="user[user_status_eq]" id="user_status_eq">
        {{ options(Users.USER_STATUS, user_status) }}
    </select>

    <button type="submit" class="ui button">搜索</button>
</form>

{% macro avatar_image(user) %}
    <img src="{{ user.avatar_small_url }}" height="50"/>
{% endmacro %}

{% macro user_info(user) %}
    姓名:{{ user.nickname }}  性别:{{ user.sex_text }} 段位:{{ user.segment_text }}<br/>
    魅力值:{{ user.charm_value }} 财富值:{{ user.wealth_value }}<br/>
    手机号码:{{ user.mobile }}<br/>
    设备ID:<a href="/admin/devices?device[id_eq]={{ user.device_id }}">{{ user.device_id }}</a><br/>
    经纬度定位: {{ user.geo_province_name }}, {{ user.geo_city_name }}<br/>
    IP定位: {{ user.ip_province_name }}, {{ user.ip_city_name }}<br/>
    自述城市信息: {{ user.province_name }}, {{ user.city_name }}<br/>
    国家:{% if user.country %}{{ user.country.chinese_name }}{% endif %}<br/>
    邮箱:{{ user.login_name }}
{% endmacro %}

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


{% macro profile_link(user) %}
    {% if isAllowed('users','detail') %}
        <a href="/admin/users/detail?id={{ user.id }}">详情</a><br/>
    {% endif %}
    {% if isAllowed('users','edit') %}
        <a class="modal_action" href="/admin/users/edit?id={{ user.id }}">编辑</a><br/>
    {% endif %}
    {% if isAllowed('rooms','index') %}
        <a href="/admin/rooms?room[id_eq]={{ user.room_id }}">房间</a>
        {% if user.current_room_id %}
            <a href="/admin/rooms?room[id_eq]={{ user.current_room_id }}">所在房间</a>
        {% endif %}
        <br/>
    {% endif %}
    {% if isAllowed('unions','index') %}
        {% if user.union %}
            {% if user.union.type == 1 %}
                <a href="/admin/unions?union_id={{ user.union_id }}">工会</a>
            {% else %}
                <a href="/admin/unions/family?id={{ user.union_id }}">家族</a>
            {% endif %}
            <br/>
        {% endif %}
    {% endif %}
    {% if isAllowed('users','send_message') %}
        <a href="/admin/users/send_message?id={{ user.id }}" class="modal_action">发送系统消息</a><br/>
    {% endif %}
    {% if isAllowed('users','getui') %}
        <a href="/admin/users/getui?receiver_id={{ user.id }}" class="modal_action">发送个推消息</a><br/>
    {% endif %}
    {% if isAllowed('users','unbind_third_account') %}
        <a href="/admin/users/unbind_third_account?id={{ user.id }}" id="unbind_third_account">解绑第三方账号</a><br/>
    {% endif %}
    {% if isAllowed('users','join_company') %}
        <a href="/admin/users/join_company?id={{ user.id }} " id="join_company">加入公司内部成员</a><br/>
    {% endif %}
    {% if isAllowed('users','delete_user_login_info') %}
        <a href="/admin/users/delete_user_login_info?id={{ user.id }} " id="delete_user_login_info">清除用户登录信息</a><br/>
    {% endif %}
{% endmacro %}

{{ simple_table(users,['id': 'id','uid': 'uid','头像': 'avatar_image', '渠道信息:':'product_channel_view', '用户信息':'user_info',
    '状态':'user_status_info', '操作':'profile_link'
]) }}

<script type="text/template" id="user_tpl">
    <tr id="user_${user.id}">
        <td>${user.id}</td>
        <td>${user.uid}</td>
        <td><img src="${ user.avatar_small_url }" height="50"/></td>
        <td>
            产品渠道:${ user.product_channel_name }<br/>
            FR:${ user.fr }<br/>
            FR名称:${ user.partner_name }<br/>
            平台:${ user.platform } 平台版本:${ user.platform_version }<br/>
            版本名称:${ user.version_name } 软件版本号:${ user.version_code }<br/>
            api协议版本: ${ user.api_version }<br/>
        </td>

        <td>
            姓名:${ user.nickname } 性别:${ user.sex_text }<br/>
            手机号码:${ user.mobile }<br/>
            设备ID:<a href="/admin/devices?device[id_eq]=${user.device_id}">${user.device_id}</a><br/>
            经纬度定位: ${ user.geo_province_name }, ${ user.geo_city_name }<br/>
            IP定位: ${ user.ip_province_name }, ${ user.ip_city_name }<br/>
            自述城市信息: ${ user.province_name }, ${ user.city_name }
            国家: ${ user.country_chinese_name }
        </td>
        <td>
            ${ user.user_type_text } | ${ user.user_status_text }<br/>
            激活时间: ${ user.created_at_text }<br/>
            注册时间: ${ user.register_at_text }<br/>
            最后活跃时间: ${ user.last_at_text }<br/>
            登录方式: ${ user.login_type_text }<br/>
            用户等级: ${ user.level }
        </td>
        <td>
            {% if isAllowed('users','detail') %}
                <a href="/admin/users/detail?id=${ user.id }">详情</a><br/>
            {% endif %}
            {% if isAllowed('users','edit') %}
                <a href="/admin/users/edit/${user.id}" class="modal_action">编辑</a><br/>
            {% endif %}
            {% if isAllowed('rooms','index') %}
                <a href="/admin/rooms?room[id_eq]=${ user.room_id }">房间</a>
                {@if user.current_room_id }
                <a href="/admin/rooms?room[id_eq]=${ user.current_room_id }">所在房间</a>
                {@/if}
                <br/>
            {% endif %}
            {% if isAllowed('users','send_message') %}
                <a href="/admin/users/send_message?id=${ user.id }" class="modal_action">发送系统消息</a><br/>
            {% endif %}
            {% if isAllowed('users','getui') %}
                <a href="/admin/users/getui?receiver_id=${ user.id }" class="modal_action">发送个推消息</a><br/>
            {% endif %}
            {% if isAllowed('users','unbind_third_account') %}
                <a href="/admin/users/unbind_third_account?id=${ user.id }" id="unbind_third_account">解绑第三方账号</a><br/>
            {% endif %}
            {% if isAllowed('users','join_company') %}
                <a href="/admin/users/join_company?id=${ user.id }" id="join_company">加入公司内部成员</a><br/>
            {% endif %}
            {% if isAllowed('users','delete_user_login_info') %}
                <a href="/admin/users/delete_user_login_info?id=${ user.id }" id="delete_user_login_info">清除用户登录信息</a><br/>
            {% endif %}
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

    $('body').on('click', '#unbind_third_account', function (e) {
        e.preventDefault();
        if (confirm('确认解绑？')) {
            var href = $(this).attr('href');
            $.post(href, '', function (resp) {
                alert(resp.error_reason);
                if (resp.error_code == 0) {
                    location.href = resp.error_url;
                }
            });
        }
    });
    $('body').on('click', '#join_company', function (e) {
        e.preventDefault();
        if (confirm('确认加入？')) {
            var href = $(this).attr('href');
            $.post(href, '', function (resp) {
                alert(resp.error_reason);
                location.reload(true)
            });
        }
    });
    $('body').on('click', '#delete_user_login_info', function (e) {
        e.preventDefault();
        if (confirm('确认清除？')) {
            var href = $(this).attr('href');
            $.post(href, '', function (resp) {
                alert(resp.error_reason);
                location.reload(true)
            });
        }
    });
</script>
