<form action="/admin/devices" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="device[product_channel_id_eq]" id="product_channel_id_eq">
        {{ options(product_channels,'','id','name') }}
    </select>

    <label for="id_eq">ID</label>
    <input name="device[id_eq]" type="text" id="id_eq"/>
    <label for="id_eq">用户ID</label>
    <input name="device[user_id_eq]" type="text" id="user_id_eq"/>

    <label for="device_no_eq">设备号</label>
    <input name="device[device_no_eq]" type="text" id="device_no_eq"/>

    <label for="imei_eq">IMEI</label>
    <input type="text" name="device[imei_eq]" id="imei_eq">

    <label for="idfa_eq">IDFA</label>
    <input type="text" name="device[idfa_eq]" id="idfa_eq">

    <button type="submit" class="ui button">搜索</button>
</form>

{% if isAllowed('devices', 'export') %}
    <br/>
    <form action="/admin/devices/export" target="_blank" method="get" class="search_form" autocomplete="off">

        <label for="export_column">
            设备标识
        </label>
        <select name="export_column" id="export_column">
            {{ options(export_columns, '') }}
        </select>

        <label for="start_at">开始时间</label>
        <input type="text" name="start_at" class="form_datetime" id="start_at" value="{{ date('Y-m-d') }}" size="16">

        <label for="end_at">结束时间</label>
        <input type="text" name="end_at" class="form_datetime" id="end_at" value="{{ date('Y-m-d') }}" size="16">

        <button type="submit" class="ui button">导出标识</button>
    </form>
{% endif %}

{%- macro platform_info(device) %}
    平台:{{ device.platform }}<br/>
    平台版本:{{ device.platform_version }}<br/>
    版本名字:{{ device.version_name }}<br/>
    版本号:{{ device.version_code }}<br/>
    api协议版本: {{ device.api_version }}<br/>
{%- endmacro %}

{%- macro device_info(device) %}
    设备号:{{ device.device_no }}<br/>
    SID:{{ device.sid }}<br/>
    IMEI:{{ device.imei }}<br/>
    IMSI:{{ device.imsi }}<br/>
    IDFA:{{ device.idfa }}<br/>
    经纬度定位: {{ device.geo_province_name }}, {{ device.geo_city_name }}<br/>
    IP定位: {{ device.province_name }}, {{ device.city_name }}<br/>
{%- endmacro %}

{%- macro users_link(object) %}
    FR:{{ object.fr }}<br/>
    渠道:{{ object.partner_name }}<br/>
    注册数:{{ object.reg_num }}<br/>
    用户ID:<a href="/admin/users?user[device_id_eq]={{ object.id }}">{{ object.user_id }}</a><br/>
    最后活跃时间: {{ object.last_at_text }}
{%- endmacro %}

{{ simple_table(devices, [
'ID': 'id', '时间': 'created_at_text', '产品渠道':'product_channel_name','设备信息': 'device_info',
'平台': 'platform_info','注册个数': 'users_link',
'状态': 'status_text','编辑':'edit_link'
]
) }}


<script type="text/template" id="device_tpl">
    <tr id="device_${device.id}">
        <td>${device.id}</td>
        <td>${device.created_at_text}</td>
        <td>${device.product_channel_name}</td>
        <td>
            设备号:${ device.device_no }<br/>
            SID:${ device.sid }<br/>
            IMEI:${ device.imei}<br/>
            IMSI:${ device.imsi }<br/>
            IDFA:${ device.idfa }<br/>
            经纬度定位: ${ device.geo_province_name }, ${ device.geo_city_name }<br/>
            IP定位: ${ device.province_name }, ${ device.city_name }<br/>
        </td>
        <td>
            平台:${device.platform}<br/>
            平台版本:${ device.platform_version}<br/>
            版本名字:${ device.version_name }<br/>
            版本号:${ device.version_code }<br/>
        </td>
        <td>
            FR:${ device.fr }<br/>
            渠道:${ device.partner_name }<br/>
            注册数:${device.reg_num}<br/>
            用户ID:<a href="/admin/users?user[device_id_eq]==${device.id}">${device.user_id}</a><br/>
            最后活跃时间: ${ device.last_at_text }
        </td>
        <td>${device.status_text}</td>
        <td>
            <a href="/admin/devices/edit/${device.id}" class="modal_action">编辑</a>
        </td>

    </tr>
</script>

<script type="text/javascript">
    $(".form_datetime").datetimepicker({
        language: "zh-CN",
        format: 'yyyy-mm-dd',
        autoclose: 1,
        todayBtn: 1,
        todayHighlight: 1,
        startView: 2,
        minView: 2
    });
</script>
