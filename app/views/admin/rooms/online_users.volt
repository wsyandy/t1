{% macro avatar_image(user) %}
    <img src="{{ user.avatar_small_url }}" height="50"/>
{% endmacro %}

{% macro user_info(user) %}
    姓名:{{ user.nickname }}  性别:{{ user.sex_text }}<br/>
    手机号码:{{ user.mobile }}<br/>
    经纬度定位: {{ user.geo_province_name }}, {{ user.geo_city_name }}<br/>
    IP定位: {{ user.ip_province_name }}, {{ user.ip_city_name }}<br/>
    自述城市信息: {{ user.province_name }}, {{ user.city_name }}<br/>
    角色: {{ user.user_role_text }}<br>
    FD: {{ user.user_fd }}<br>
{% endmacro %}

{% macro user_status_info(user) %}
    {{ user.user_type_text }} | {{ user.user_status_text }}<br/>
    最后活跃时间: {{ user.last_at_text }}<br/>
    api协议版本: {{ user.api_version }}
{% endmacro %}

{% macro product_channel_view(user) %}
    产品渠道:{{ user.product_channel_name }}<br/>
    FR:{{ user.fr }}<br/>
    FR名称:{{ user.partner_name }}<br/>
    注册时间: {{ user.created_at_text }}<br/>
    平台:{{ user.platform }}<br/>
    设备ID:<a href="/admin/devices?device[id_eq]={{ user.device_id }}">{{ user.device_id }}</a><br/>
{% endmacro %}


{% macro msg_link(user) %}
    {#<a href="/admin/rooms/send_msg?user_id={{ user.id }}" class="modal_action">模拟消息</a><br/>#}
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

{{ simple_table(users,['用户id': 'id','头像': 'avatar_image', '渠道信息:':'product_channel_view', '用户信息':'user_info',
'状态':'user_status_info','模拟消息':'msg_link'
]) }}