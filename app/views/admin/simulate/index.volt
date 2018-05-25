<form action="/admin/simulate" method="get" class="search_form" autocomplete="off" id="search_form">
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
    <a href="/admin/rooms/send_topic_msg?user_id={{ user.id }}" class="modal_action">发公屏消息</a><br/>
    <a href="/admin/rooms/enter_room?user_id={{ user.id }}" class="modal_action">进房间</a>
    <a href="/admin/rooms/exit_room?user_id={{ user.id }}" class="modal_action">退房间</a><br/>
    <a href="/admin/rooms/send_gift?user_id={{ user.id }}" class="modal_action">送礼物</a><br/>
    <a href="/admin/rooms/up?user_id={{ user.id }}" class="modal_action">上麦</a>
    <a href="/admin/rooms/down?user_id={{ user.id }}" class="modal_action">下麦</a><br/>
    <a href="/admin/rooms/hang_up?user_id={{ user.id }}" class="modal_action">挂断电话</a><br/>
    <a href="/admin/rooms/room_notice?user_id={{ user.id }}" class="modal_action">房间信息通知</a><br/>
    <a href="/admin/rooms/red_packet?user_id={{ user.id }}" class="modal_action">红包</a>
    <a href="/admin/rooms/pk?user_id={{ user.id }}" class="modal_action">PK</a><br/>
    <a href="/admin/rooms/boom_gift?user_id={{ user.id }}" class="modal_action">爆礼物</a>
    <a href="/admin/rooms/sink_notice?user_id={{ user.id }}" class="modal_action">下沉通知</a><br/>
{% endmacro %}

{{ simple_table(users,['id': 'id','uid': 'uid','头像': 'avatar_image', '渠道信息:':'product_channel_view', '用户信息':'user_info',
'状态':'user_status_info', '操作':'profile_link'
]) }}

<script type="text/template" id="simulate_tpl">
    <tr id="simulate_${user.id}">
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
            <a href="/admin/rooms/send_topic_msg?user_id={{ user.id }}" class="modal_action">发公屏消息</a><br/>
            <a href="/admin/rooms/enter_room?user_id={{ user.id }}" class="modal_action">进房间</a>
            <a href="/admin/rooms/exit_room?user_id={{ user.id }}" class="modal_action">退房间</a><br/>
            <a href="/admin/rooms/send_gift?user_id={{ user.id }}" class="modal_action">送礼物</a><br/>
            <a href="/admin/rooms/up?user_id={{ user.id }}" class="modal_action">上麦</a>
            <a href="/admin/rooms/down?user_id={{ user.id }}" class="modal_action">下麦</a><br/>
            <a href="/admin/rooms/hang_up?user_id={{ user.id }}" class="modal_action">挂断电话</a><br/>
            <a href="/admin/rooms/room_notice?user_id={{ user.id }}" class="modal_action">房间信息通知</a><br/>
            <a href="/admin/rooms/red_packet?user_id={{ user.id }}" class="modal_action">红包</a>
            <a href="/admin/rooms/pk?user_id={{ user.id }}" class="modal_action">PK</a><br/>
            <a href="/admin/rooms/boom_gift?user_id={{ user.id }}" class="modal_action">爆礼物</a>
            <a href="/admin/rooms/sink_notice?user_id={{ user.id }}" class="modal_action">下沉通知</a><br/>
        </td>

    </tr>
</script>