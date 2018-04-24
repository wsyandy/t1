<form action="/admin/users/company_user" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="company_user[product_channel_id_eq]" id="product_channel_id_eq">
        {{ options(product_channels,'','id','name') }}
    </select>

    <label for="id_eq">ID</label>
    <input name="company_user[id_eq]" type="text" id="id_eq"/>

    <label for="mobile">手机号</label>
    <input name="company_user[mobile_eq]" type="text" id="mobile"/>

    <label for="user_type">类型</label>
    <select name="company_user[user_type_eq]" id="user_type_eq">
        {{ options(user_types, '') }}
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
    自述城市信息: {{ user.province_name }}, {{ user.city_name }}
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
    {% if isAllowed('users','clear_company_user') %}
        <a href="/admin/users/clear_company_user?id={{ user.id }} " id="clear_company_user">删除</a><br/>
    {% endif %}
{% endmacro %}

{{ simple_table(users,['用户id': 'id','头像': 'avatar_image', '渠道信息:':'product_channel_view', '用户信息':'user_info',
    '状态':'user_status_info', '操作':'profile_link'
]) }}

<script>
    $('body').on('click', '#clear_company_user', function (e) {
        e.preventDefault();
        if (confirm('确认删除？')) {
            var href = $(this).attr('href');
            $.post(href, '', function (resp) {
                alert(resp.error_reason);
                location.reload(true)
            });
        }
    });
</script>