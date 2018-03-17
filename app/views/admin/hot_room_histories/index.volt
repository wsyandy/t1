{%- macro user_link(hot_room_history) %}
    姓名：<a href="/admin/users/index?user[id_eq]={{ hot_room_history.user_id }}">{{ hot_room_history.user_nickname }}</a>
    <br/>
    房间名：<a
        href="/admin/rooms/index?room[user_id_eq]={{ hot_room_history.user_id }}">{{ hot_room_history.user.room_name }}</a>
    <br/>
    直播简介： {{ hot_room_history.introduce }} <br/>
{% endmacro %}

{%- macro faminy_link(hot_room_history) %}
    <a href="/admin/unions/family?union[id_eq]={{ hot_room_history.union_id }}">{{ hot_room_history.union_name }}</a>
{% endmacro %}

{%- macro time(hot_room_history) %}
    {{ hot_room_history.start_at_text }}-{{ hot_room_history.end_at_text }}
{% endmacro %}

{%- macro operation_link(hot_room_history) %}
    {% if isAllowed('hot_room_histoies','edit') and 3 == hot_room_history.status %}
        <a href="/admin/hot_room_histories/edit/{{ hot_room_history.id }}" class="modal_action">编辑</a><br/>
    {% endif %}
{% endmacro %}

{{ simple_table(hot_room_histories,["ID":'id','家族名称':'faminy_link','用户信息':'user_link','状态':"status_text",'申请时间段':'time','操作':'operation_link']) }}