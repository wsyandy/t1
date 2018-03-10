<form action="/admin/rooms/earnings" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="id_eq">ID</label>
    <input name="room[id_eq]" type="text" id="id_eq"/>

    <label for="user_id_eq">房主ID</label>
    <input name="room[user_id_eq]" type="text" id="user_id_eq"/>

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

{% macro earnings_detail(room) %}
    <a href="/admin/rooms/earnings_detail?id={{ room.id }}">明细</a><br/>
{% endmacro %}

{% macro avatar_image(room) %}
    <img src="{{ room.user_avatar_url }}" height="50" width="50"/>
{% endmacro %}

{{ simple_table(rooms,['id': 'id','头像':'avatar_image','房间信息':'room_info','房主信息':"user_info","房主收益(钻石)":"amount","明细":"earnings_detail"]) }}
