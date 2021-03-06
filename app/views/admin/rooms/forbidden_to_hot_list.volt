
{% macro user_info(room) %}
    {% if isAllowed('users','index') %}
        姓名:<a href="/admin/users?user[id_eq]={{ room.user_id }}">{{ room.user_nickname }}</a><br/>
    {% endif %}
    房主ID:{{ room.user.id }}<br/>
    房主UID:{{ room.user.uid }}<br/>
    性别:{{ room.user.sex_text }}<br/>
    手机号码:{{ room.user_mobile }}<br/>
{% endmacro %}

{% macro room_info(room) %}
    房间名称: {{ room.name }}<br/>
    房间话题: {{ room.topic }}<br/>
    在线人数: {{ room.user_num }} 主题类型: {{ room.theme_type_text }}<br/>
    {% if room.audio_id > 0 %}
        音频ID:<a href="/admin/audios?audio[id_eq]={{ room.audio_id }}">{{ room.audio_id }}</a><br/>
    {% endif %}
    是否热门：{{ room.hot_text }}
{% endmacro %}

{% macro room_status_info(room) %}
    房间: {{ room.status_text }}|房主:{{ room.online_status_text }}|用户:{{ room.user_type_text }}<br/>
    最后活跃时间: {{ room.last_at_text }}<br/>
    公频聊天状态: {{ room.chat_text }}<br/>
    是否加锁: {{ room.lock_text }}<br/>
    是否热门: {{ room.hot_text }}|是否置顶: {{ room.top_text }}|是否最新: {{ room.new_text }}<br/>
    协议: {{ intval(room.user_agreement_num) }}<br/>
    {% if room.union_id %}
        公会: {{ room.union.name }}<br/>
        公会类型: {{ room.union.type_text }}<br/>
    {% endif %}
{% endmacro %}


{% macro operate_link(room) %}
    {% if isAllowed('rooms','rem_forbidden_list') %}
        <a href="/admin/rooms/rem_forbidden_list?id={{ room.id }}" id="rem_forbidden_list">删除</a></br>
    {% endif %}
{% endmacro %}

{% macro avatar_image(room) %}
    <img src="{{ room.user_avatar_url }}" height="50" width="50"/>
{% endmacro %}

{{ simple_table(rooms,['id': 'id','uid': 'uid','头像':'avatar_image','房间信息':'room_info','房主信息':"user_info",'房间状态':'room_status_info',"操作":"operate_link"]) }}

<script type="text/template" id="room_tpl">
    <tr id="room_${room.id}">
        <td>${room.id}</td>
        <td>${room.uid}</td>
        <td><img src="${room.avatar_small_url}" height="50" width="50"/></td>
        <td>
            房间名称: ${ room.name }<br/>
            房间话题: ${ room.topic }<br/>
            在线人数: ${ room.user_num } 主题类型: ${ room.theme_type_text }<br/>
            {@if room.audio_id > 0 }
            音频ID:<a href="/admin/audios?audio[id_eq]=${ room.audio_id }">${ room.audio_id }</a><br/>
            {@/if}
            是否热门：${ room.hot_text }
        </td>
        <td>
            {% if isAllowed('users','index') %}
                姓名:<a href="/admin/users?user[id_eq]=${ room.user_id }">${ room.user_nickname }</a><br/>
            {% endif %}
            房主ID:${ room.user_id }<br/>
            房主UID:${ room.user_uid }<br/>
            性别:${ room.user_sex_text }<br/>
            手机号码:${ room.user_mobile }<br/>
        </td>
        <td>
            房间: ${ room.status_text }|房主:${ room.online_status_text }|用户:${ room.user_type_text }<br/>
            最后活跃时间: ${ room.last_at_text }<br/>
            公频聊天状态: ${ room.chat_text }<br/>
            是否加锁: ${ room.lock_text }<br/>
            是否热门: ${ room.hot_text }|是否置顶: ${ room.top_text }|是否最新: ${ room.new_text }<br/>
            协议: ${room.user_agreement_num}<br/>
            {@if room.union_id }
            公会: ${ room.union_name }<br/>
            公会类型: ${ room.union_type_text }<br/>
            {@/if}
        </td>
        <td>
            {% if isAllowed('rooms','rem_forbidden_list') %}
                <a href="/admin/rooms/rem_forbidden_list?id=${ room.id }">详细</a></br>
            {% endif %}
        </td>
    </tr>
</script>

<script>
    $('body').on('click', '#rem_forbidden_list', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        if (confirm('确认清除?')) {
            $.post(href, '', function (resp) {
                alert(resp.error_reason);
                window.reload();
            })
        }
    })

</script>