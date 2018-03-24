<form action="/admin/rooms" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="room[product_channel_id_eq]" id="product_channel_id_eq">
        {{ options(product_channels,'','id','name') }}
    </select>

    <label for="status_eq">状态</label>
    <select name="room[status_eq]" id="status_eq">
        {{ options(Rooms.STATUS) }}
    </select>

    <label for="user_type_eq">房主类型</label>
    <select name="room[user_type_eq]" id="user_type_eq">
        {{ options(Rooms.USER_TYPE) }}
    </select>

    <label for="theme_type_eq">房间主题</label>
    <select name="room[theme_type_eq]" id="theme_type_eq">
        {{ options(Rooms.THEME_TYPE) }}
    </select>

    <input type="hidden" name="room[hot]" , value="{{ hot }}">

    <label for="id_eq">ID</label>
    <input name="room[id_eq]" type="text" id="id_eq"/>
    <label for="name">房间名</label>
    <input name="name" type="text" id="name"/>
    <button type="submit" class="ui button">搜索</button>
</form>

<ol class="breadcrumb">
    <li class="active">总个数 {{ total_entries }}</li>
</ol>

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
    在线人数: {{ room.user_num }} 主题类型: {{ room.theme_type_text }}<br/>
    {% if room.theme_type == ROOM_THEME_TYPE_BROADCAST %}
        音频ID:<a href="/admin/audios?audio[id_eq]={{ room.audio_id }}">{{ room.audio_id }}</a><br/>
    {% endif %}
    是否热门：{{ room.hot_text }}
{% endmacro %}

{% macro room_status_info(room) %}
    {{ room.status_text }}|{{ room.online_status_text }}|{{ room.user_type_text }}<br/>
    最后活跃时间: {{ room.last_at_text }}<br/>
    公频聊天状态: {{ room.chat_text }}<br/>
    是否加锁: {{ room.lock_text }}<br/>
    是否热门: {{ room.hot_text }}<br/>
    协议: {{ intval(room.user_agreement_num) }}<br/>
    {% if room.union_id %}
        公会: {{ room.union.name }}<br/>
        公会类型: {{ room.union.type_text }}<br/>
    {% endif %}
{% endmacro %}


{% macro operate_link(room) %}
    {% if isAllowed('rooms','detail') %}
        <a href="/admin/rooms/detail?id={{ room.id }}">详细</a></br>
    {% endif %}
    {% if isAllowed('rooms','add_user_agreement') %}
        <a href="/admin/rooms/add_user_agreement?id={{ room.id }}" class="modal_action">添加协议</a></br>
    {% endif %}
    {% if isAllowed('rooms','delete_user_agreement') %}
        <a href="/admin/rooms/delete_user_agreement?id={{ room.id }}" id="delete_user_agreement">清除协议</a></br>
    {% endif %}
    {% if isAllowed('rooms','edit') %}
        <a href="/admin/rooms/edit?id={{ room.id }}" class="modal_action">编辑</a></br>
    {% endif %}
{% endmacro %}

{% macro avatar_image(room) %}
    <img src="{{ room.user_avatar_url }}" height="50" width="50"/>
{% endmacro %}

{{ simple_table(rooms,['id': 'id','头像':'avatar_image','房间信息':'room_info','房主信息':"user_info",'房间状态':'room_status_info',"操作":"operate_link"]) }}

<script type="text/template" id="room_tpl">
    <tr id="room_${room.id}">
        <td>${room.id}</td>
        <td><img src="${room.avatar_small_url}" height="50" width="50"/></td>
        <td>
            房间名称: ${ room.name }<br/>
            房间话题: ${ room.topic }<br/>
            在线人数: ${ room.user_num } 主题类型: ${ room.theme_type_text }<br/>
            {@if room.theme_type == ROOM_THEME_TYPE_BROADCAST }
            音频ID:<a href="/admin/audios?audio[id_eq]=${ room.audio_id }">${ room.audio_id }</a><br/>
            {@/if}
            是否热门：${ room.hot_text }
        </td>
        <td>
            {% if isAllowed('users','index') %}
                姓名:<a href="/admin/users?user[id_eq]=${ room.user_id }">${ room.user_nickname }</a><br/>
            {% endif %}
            性别:${ room.user_sex_text }<br/>
            手机号码:${ room.user_mobile }<br/>
        </td>
        <td>
            ${ room.status_text }|${ room.online_status_text }|${ room.user_type_text }<br/>
            最后活跃时间: ${ room.last_at_text }<br/>
            公频聊天状态: ${ room.chat_text }<br/>
            是否加锁: ${ room.lock_text }<br/>
            是否热门: ${ room.hot_text }<br/>
            协议: ${room.user_agreement_num}<br/>
            {@if room.union_id }
            公会: ${ room.union_name }<br/>
            公会类型: ${ room.union.type_text }<br/>
            {@/if}
        </td>
        <td>
            {% if isAllowed('rooms','detail') %}
                <a href="/admin/rooms/detail?id=${ room.id }">详细</a></br>
            {% endif %}
            {% if isAllowed('rooms','add_user_agreement') %}
                <a href="/admin/rooms/add_user_agreement?id=${ room.id }" class="modal_action">添加协议</a></br>
            {% endif %}
            {% if isAllowed('rooms','delete_user_agreement') %}
                <a href="/admin/rooms/delete_user_agreement?id=${ room.id }" id="delete_user_agreement">清除协议</a></br>
            {% endif %}
            {% if isAllowed('rooms','edit') %}
                <a href="/admin/rooms/edit?id=${ room.id }" class="modal_action">编辑</a></br>
            {% endif %}
        </td>
    </tr>
</script>

<script>
    $('body').on('click', '#delete_user_agreement', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        if (confirm('确认清除?')) {
            $.post(href, '', function (resp) {
                alert(resp.error_reason);
            })
        }
    })
</script>