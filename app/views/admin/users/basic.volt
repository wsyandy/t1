<table class="table table-striped table-condensed table-hover">
    <caption>个人信息</caption>
    <tr>
        <td>sid: {{ user.sid }}</td>
        <td>注册时间: {{ user.created_at_text }}</td>
        <td>最后活跃时间: {{ user.last_at_text }}</td>
        <td>IP地址:{{ user.ip_text }}</td>
        <td>状态: {{ user.user_status_text }}</td>
    </tr>
    <tr>
        <td>姓名:{{ user.id_name }}</td>
        <td>手机号码:{{ user.mobile_text }}</td>
        <td>生日: {{ user.birthday_text }}, {{ user.age }}岁</td>
        <td>fr: {{ user.fr }}, 渠道:{{ user.partner_name }}</td>
    </tr>
</table>

<table class="table  table-condensed table-hover">
    <caption> 最近登陆设备信息</caption>
    {% for device in devices %}
        <tr>
            <td>ID: {{ device.id }}</td>
            <td>激活时间:{{ device.created_at_text }}</td>
            <td>设备平台:{{ device.platform }} {{ device.platform_version }} </td>
            <td>手机型号:{{ device.manufacturer }} - {{ device.model }}</td>
            <td>网络: {{ device.net }} {{ device.ip_text }} </td>
            <td>激活次数: {{ device.active_num }}</td>
        </tr>

        <tr>
            <td>sdks:{{ device.sdks }}</td>
            <td>api版本:{{ device.api_version }}</td>
            <td>经纬度:{{ device.latitude }} {{ device.longitude }}</td>
            <td>状态:{{ device.status_text }}</td>
            <td>登陆次数:{{ device.login_num }}</td>
            <td>设备No: {{ device.device_no }}</td>
        </tr>
        <tr>
            <td>渠道</td>
            <td>fr:{{ device.fr }}</td>
            <td>渠道:{{ device.partner_name }}</td>
            <td></td>
            <td></td>
            <td>最后登陆时间: {{ device.last_at_text }}</td>
        </tr>
    {% endfor %}
</table>
