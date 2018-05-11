<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>时间</th>
        <th>总分值</th>
        <th>赠送礼物钻石数分值</th>
        <th>赠送礼物次数分值</th>
        <th>在线人数分值</th>
        <th>用户停留时长分值</th>
        <th>房主分值</th>
        <th>主持认证用户分值</th>
    </tr>
    </thead>
    <tbody id="stat_list">

    {% if scores %}
        <tr class="row_line">
            <td>{{ date("Y-m-d H:i:s", scores['time']) }}</td>
            <td>{{ scores['total_score'] }}</td>
            <td>{{ scores['send_gift_amount_score'] }}</td>
            <td>{{ scores['send_gift_num_score'] }}</td>
            <td>{{ scores['real_user_pay_score'] }}</td>
            <td>{{ scores['real_user_stay_time_score'] }}</td>
            <td>{{ scores['room_host_score'] }}</td>
            <td>{{ scores['id_card_auth_users_score'] }}</td>
        </tr>
    {% endif %}
    </tbody>
</table>