{% macro user_info(room_seat) %}
    用户ID:{{ room_seat.user_id }}  <br/>
    用户名:{{ room_seat.user_nickname }}<br/>
{% endmacro %}

{%- macro operat_link(room_seat) %}
    {% if  isAllowed('broadcasts','index') %}
        <a href="/admin/broadcasts/compile_room_seat?seat_id={{ room_seat.id }}" class="modal_action">编辑</a>
    {% endif %}
{%- endmacro %}

{{ simple_table(room_seats,['id': 'id','状态': 'status_text','麦克风状态':'microphone_text','音乐权限':'can_play_music_text',
'用户':'user_info','操作':'operat_link'
]) }}