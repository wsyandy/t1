{% macro avatar_image(user) %}
    <img src="{{ user.avatar_small_url }}" height="50"/>
{% endmacro %}

{% macro user_info(user) %}
    姓名:{{ user.nickname }}  性别:{{ user.sex_text }}<br/>
    手机号码:{{ user.mobile }}<br/>
    经纬度定位: {{ user.geo_province_name }}, {{ user.geo_city_name }}<br/>
    IP定位: {{ user.ip_province_name }}, {{ user.ip_city_name }}<br/>
    自述城市信息: {{ user.province_name }}, {{ user.city_name }}
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



{% macro detail_link(user) %}
    <a href="/admin/users/detail?id={{ user.id }}">征信信息</a>
{% endmacro %}

{{ simple_table(users,['用户id': 'id','头像': 'avatar_image', '渠道信息:':'product_channel_view', '用户信息':'user_info',
'状态':'user_status_info'
]) }}