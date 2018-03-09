<form action="/admin/rooms" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="room[product_channel_id_eq]" id="product_channel_id_eq">
        {{ options(product_channels,'','id','name') }}
    </select>

    <label for="id_eq">ID</label>
    <input name="room[id_eq]" type="text" id="id_eq"/>
    <label for="name">房间名</label>
    <input name="name" type="text" id="name"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{% macro user_info(room) %}
    {% if isAllowed('users','index') %}
        姓名:<a href="/admin/users?user[id_eq]={{ room.user_id }}">{{ room.user_nickname }}</a><br/>
    {% endif %}
    性别:{{ room.user.sex_text }}<br/>
    手机号码:{{ room.user_mobile }}<br/>
{% endmacro %}

{% macro room_info(room) %}
    房间名称: {{ room.name }}<br/>
    房间话题: {{ room.topic }}<br/>
{% endmacro %}

{% macro room_earnings_link(room) %}
    房主收到的礼物:<a
        href="/admin/gift_orders?gift_order[room_id_eq]={{ room.id }}&gift_order[user_id_eq]={{ room.user_id }}">{{ room.room_diamond }}钻石</a>
    <br/>
{% endmacro %}


{% macro avatar_image(room) %}
    <img src="{{ room.user_avatar_url }}" height="50" width="50"/>
{% endmacro %}

{{ simple_table(rooms,['id': 'id','头像':'avatar_image','房间信息':'room_info','房主信息':"user_info","房主收益":"room_earnings_link"]) }}
