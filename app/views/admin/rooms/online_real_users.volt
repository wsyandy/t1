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
    麦位ID: {{ user.current_room_seat_id }}<br>
{% endmacro %}

{% macro user_status_info(user) %}
    {{ user.user_type_text }} | {{ user.user_status_text }}<br/>
    最后活跃时间: {{ user.last_at_text }}<br/>
    api协议版本: {{ user.api_version }}
{% endmacro %}

{% macro product_channel_view(user) %}
    产品渠道:{{ user.product_channel_name }}<br/>
    FR:{{ user.fr }}  FR名称:{{ user.partner_name }}<br/>
    平台:{{ user.platform }} 平台版本:{{ user.platform_version }}<br/>
    版本名称:{{ user.version_name }} 软件版本号:{{ user.version_code }}<br/>
    设备ID:<a href="/admin/devices?device[id_eq]={{ user.device_id }}">{{ user.device_id }}</a><br/>
{% endmacro %}


{% macro send_topic_msg(user) %}
    <a href="/admin/rooms/send_topic_msg?user_id={{ user.id }}" class="modal_action">发公屏消息</a><br/>
{% endmacro %}

{% macro msg_link(user) %}
    <a href="/admin/rooms/kicking?id={{ user.current_room_id }}&user_id={{ user.id }}" data-user_id="{{ user.id }}"
       class="kicking">踢出</a><br/>
{% endmacro %}

{{ simple_table(users,['用户id': 'id','头像': 'avatar_image', '渠道信息:':'product_channel_view', '用户信息':'user_info',
    '状态':'user_status_info','模拟消息':'msg_link'
]) }}

<script type="text/javascript">

    $(function () {

        $("#user_list").on('click', '.kicking', function (e) {
            e.preventDefault();
            var href = $(this).attr('href');
            var self = $(this);
            var user_id = self.data('user_id');

            if (confirm('确认踢出')) {
                $.post(href, '', function (resp) {
                    $("#user_" + user_id).remove();
                    alert(resp.error_reason);
                    return false;
                })
            }

            return false;
        })
    })

</script>
