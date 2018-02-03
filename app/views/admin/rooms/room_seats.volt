{% macro user_info(room_seat) %}
    用户ID:{{ room_seat.user_id }}  <br/>
    用户名:{{ room_seat.user_nickname }}<br/>
{% endmacro %}

{% macro send_gift_link(room_seat) %}
    {% if isAllowed('room','give_gift') and room_seat.user_id != 0 %}
        <a href="/admin/rooms/give_gift?user_id={{ room_seat.user_id }}" class="modal_action">送礼物</a><br/>
    {% else %}
        不可送礼物<br/>
    {% endif %}
{% endmacro %}

{{ simple_table(room_seats,['id': 'id','状态': 'status_text','麦克风状态':'microphone_text','用户':'user_info','送TA礼物':'send_gift_link'
]) }}