<table class="table table-striped table-condensed table-hover">
    <caption>个人信息</caption>
    <tr>
        <td>sid: {{ user.sid }}</td>
        <td>
            {% if isAllowed('users', 'reset_uid') %}
                <a href="/admin/users/reset_uid?id={{ user.id }}" class="modal_action">重置用户ID</a>
            {% endif %}
        </td>
        <td>注册时间: {{ user.created_at_text }}</td>
        <td>最后活跃时间: {{ user.last_at_text }}</td>
    </tr>
    <tr>
        <td>昵称:{{ user.nickname }}</td>
        <td>状态: {{ user.user_status_text }} 登录方式: {{ user.login_type_text }}</td>
        <td>手机号码:{{ user.mobile }}</td>
        <td>第三方登录标识: {{ user.third_unionid }}</td>
    </tr>
    <tr>
        <td>实名认证：{{ user.id_card_auth_text }}</td>
        {#<td>星座: {{ user.constellation_text }} 身高: {{ user.height }}</td>#}
        <td>粉丝人数: {{ user.followed_num }} 关注数: {{ user.follow_num }} 好友人数: {{ user.friend_num }}</td>
        <td>fr: {{ user.fr }}, 渠道:{{ user.partner_name }}</td>
        <td>头像审核状态:{{ user.avatar_status_text }}</td>
    </tr>
    <tr>
        <td>IP:{{ user.ip }}</td>
        <td>经纬度：{{ user.latitude }}，{{ user.longitude }}</td>
        <td>
            钻石: <a href="/admin/account_histories?user_id={{ user.id }}">{{ user.diamond }}</a>
            hi币：{{ user.hi_coins }}
        </td>
        <td><a href="/admin/users/reset_password?id={{ user.id }}" class="modal_action">重置密码</a></td>
    </tr>
    <tr>
        <td>用户等级:{{ user.level }}</td>
        <td>用户经验:{{ user.experience }}</td>
        <td>
            国际版段位:{{ user.i_segment_text }}
            段位:{{ user.segment_text }}
        </td>
        <td>充值金额:{{ user.pay_amount }}</td>
    </tr>
    <tr>
        <td>经纬度定位: {{ user.geo_province_name }}, {{ user.geo_city_name }}</td>
        <td>IP定位: {{ user.ip_province_name }}, {{ user.ip_city_name }}</td>
        <td>自述城市信息: {{ user.province_name }}, {{ user.city_name }}</td>
        <td>个性签名: {{ user.monologue }}</td>
    </tr>
    <tr>
        <td>魅力值:{{ user.charm_value }}</td>
        <td>财富值:{{ user.wealth_value }}</td>
        <td>家族魅力值:{{ user.union_charm_value }}</td>
        <td>家族财富值:{{ user.union_wealth_value }}</td>
    </tr>
    <tr>
        <td>被封原因:{{ user.blocked_reason }}</td>
        <td>生日: {{ user.birthday_text }}, {{ user.age }}岁</td>
        <td></td>
        <td></td>
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
        </tr>

        <tr>
            <td>api版本:{{ device.api_version }}</td>
            <td>经纬度:{{ device.latitude }} {{ device.longitude }}</td>
            <td>状态:{{ device.status_text }}</td>
            <td>设备No: {{ device.device_no }}</td>
            <td>推广fr: {{ device.fr }}</td>
            <td>渠道:{{ device.partner_name }}</td>
        </tr>

    {% endfor %}
</table>
