<form action="/admin/rooms" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="room[product_channel_id_eq]" id="product_channel_id_eq">
        {{ options(product_channels,'','id','name') }}
    </select>

    <label for="id_eq">ID</label>
    <input name="room[id_eq]" type="text" id="id_eq"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{% macro user_info(room) %}
    姓名:{{ room.user_nickname }}  <br/>
    {#性别:{{ room.user_sex_text }}<br/>#}
    手机号码:{{ room.user_mobile }}<br/>
{% endmacro %}

{% macro room_info(room) %}
    房间名称: {{ room.name }}<br/>
    房间话题: {{ room.topic }}<br/>
    在线人数: {{ room.user_num }}<br/>
{% endmacro %}

{% macro room_status_info(room) %}
    {{ room.status_text }}|{{ room.online_status_text }}<br/>
    最后活跃时间: {{ room.last_at_text }}<br/>
    公频聊天状态: {{ room.chat_text }}<br/>
    是否加锁: {{ room.lock_text }}<br/>
{% endmacro %}


{% macro detail_link(room) %}
    {% if isAllowed('room','online_users') %}
        <a href="/admin/rooms/online_users?id={{ room.id }}">在线用户</a></br>
    {% endif %}
    {% if isAllowed('room','room_seats') %}
        <a href="/admin/rooms/room_seats?id={{ room.id }}">麦 位</a></br>
    {% endif %}
{% endmacro %}

{{ simple_table(rooms,['id': 'id','房间信息':'room_info','房主信息':"user_info",'房间状态':'room_status_info',"详细":"detail_link"]) }}
