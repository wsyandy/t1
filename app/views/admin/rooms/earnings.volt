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
    房主总收益:<a href="/admin/rooms/earnings_detail?id={{ room.id }}">{{ room.amount }}钻石</a><br/>
{% endmacro %}

{% macro avatar_image(room) %}
    <img src="{{ room.user_avatar_url }}" height="50" width="50"/>
{% endmacro %}

{{ simple_table(rooms,['id': 'id','头像':'avatar_image','房间信息':'room_info','房主信息':"user_info","房主收益":"room_earnings_link"]) }}
