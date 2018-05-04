<form action="/admin/unions/rooms" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="start_at_time_eq">开始时间</label>
    <input name="start_at_time" type="text" id="start_at_time_eq" class="form_datetime" value="{{ start_at_time }}"/>

    <label for="end_at_time_eq">结束时间</label>
    <input name="end_at_time" type="text" id="end_at_time_eq" class="form_datetime" value="{{ end_at_time }}"/>
    <input type="hidden" name="id" id="id_eq" value="{{ id }}">
    <button type="submit" class="ui button">搜索</button>
</form>
<label for="stat_at_eq">累计{{ total_amount }}</label>

{%- macro room_owner_id(room) %}
   {{ room.user.uid }}
{%- endmacro %}
{%- macro room_owner_nickname(room) %}
    {{ room.user.nickname }}
{%- endmacro %}

{{ simple_table(rooms, ['房间名': 'name','房主ID':'room_owner_id','房主名称':'room_owner_nickname',"金额":"amount"]) }}
