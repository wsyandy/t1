<form action="/admin/red_packets" method="get" class="search_form" autocomplete="off" id="search_form">

    <label for="id_eq">ID</label>
    <input name="red_packet[id_eq]" type="text" id="id_eq"/>

    <label for="user_id_eq">用户ID</label>
    <input name="red_packet[user_id_eq]" type="text" id="user_id_eq"/>

    <label for="room_id_eq">房间ID</label>
    <input name="red_packet[room_id_eq]" type="text" id="room_id_eq"/>

    <label for="status">状态</label>
    <select name="red_packet[status_eq]" id="status_eq">
        {{ options(RedPackets.STATUS) }}
    </select>

    <button type="submit" class="ui button">搜索</button>
</form>

{%- macro red_packet_type_link(red_packet_history) %}
    领取类型:{{ red_packet_history.red_packet_type_text }}<br/>
    {% if red_packet_history.red_packet_type == 'nearby' %}
        可领取性别限制：{{ red_packet_history.sex_text }}<br/>
        可领取距离限制：{{ red_packet_history.nearby_distance }}<br/>
    {% endif %}
{%- endmacro %}

{{ simple_table(red_packet_histories,['id': 'id','房间ID':'room_id','红包发起者':'user_nickname','红包限制':'red_packet_type_link','可领取个数':'num','总金额':'diamond','红包状态':'status_text',
'创建时间':'created_at_text']) }}

