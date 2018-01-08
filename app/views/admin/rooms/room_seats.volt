{% macro user_info(room_seat) %}
    用户ID:{{ room_seat.user_id }}  <br/>
    用户名:{{ room_seat.user_nickname }}<br/>
{% endmacro %}

{{ simple_table(room_seats,['id': 'id','状态': 'status_text','麦克风状态':'microphone_text','用户':'user_info'
]) }}