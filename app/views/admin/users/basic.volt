<table class="table table-striped table-condensed table-hover">
    <caption>个人信息</caption>
    <tr>
        <td>sid: {{ user.sid }}</td>
        <td>注册时间: {{ user.created_at_text }}</td>
        <td>最后活跃时间: {{ user.last_at_text }}</td>
        <td>状态: {{ user.user_status_text }}</td>
    </tr>
    <tr>
        <td>昵称:{{ user.nickname }}</td>
        <td>手机号码:{{ user.mobile }}</td>
        <td>生日: {{ user.birthday_text }}, {{ user.age }}岁</td>
        <td>个性签名: {{ user.monolog }}</td>
    </tr>
    <tr>
        <td>星座: {{ user.constellation_text }} 身高: {{ user.height }}</td>
        <td>粉丝人数: {{ user.followed_num }} 关注数: {{ user.follow_num }} 好友人数: {{ user.friend_num }}</td>
        <td>fr: {{ user.fr }}, 渠道:{{ user.partner_name }}</td>
        <td>省份: {{ user.province_name }} 城市: {{ user.city_name }}</td>
    </tr>
    <tr>
        <td>IP:{{ user.ip }}</td>
        <td>经纬度：{{ user.latitude }}，{{ user.longitude }}</td>
        <td>钻石: <a href="/admin/account_histories?user_id={{ user.id }}">{{ user.diamond }}</a></td>
        <td><a href="/admin/users/reset_password?id={{ user.id }}" class="modal_action">重置密码</a></td>
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
