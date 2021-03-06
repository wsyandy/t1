<form action="/admin/rooms" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="room[product_channel_id_eq]" id="product_channel_id_eq">
        {{ options(product_channels, product_channel_id,'id','name') }}
    </select>

    <label for="status_eq">状态</label>
    <select name="room[status_eq]" id="status_eq">
        {{ options(Rooms.STATUS, status) }}
    </select>

    <label for="user_type_eq">房主类型</label>
    <select name="room[user_type_eq]" id="user_type_eq">
        {{ options(Rooms.USER_TYPE, user_type) }}
    </select>

    <label for="theme_type_eq">房间主题</label>
    <select name="room[theme_type_eq]" id="theme_type_eq">
        {{ options(Rooms.THEME_TYPE, theme_type) }}
    </select>

    <label for="theme_type_eq">房间类型</label>
    <select name="room[types_eq]" id="types_eq">
        {{ options(Rooms.TYPES, types) }}
    </select>

    <input type="hidden" name="room[hot]" , value="{{ hot }}">

    <label for="id_eq">ID</label>
    <input name="room[id_eq]" type="text" id="id_eq" value="{{ id }}"/>

    <label for="uid_eq">UID</label>
    <input name="room[uid_eq]" type="text" id="uid_eq" value="{{ uid }}"/>

    <label for="name">房间名</label>
    <input name="name" type="text" id="name" value="{{ name }}"/>

    <label for="user_id_eq">房主ID</label>
    <input name="user_id" type="text" id="user_id_eq" value="{{ user_id }}"/>

    <label for="user_uid">房主UID</label>
    <input name="user_uid" type="text" id="user_uid" value="{{ user_uid }}"/>

    <button type="submit" class="ui button">搜索</button>
</form>

<ol class="breadcrumb">
    <li class="active">总个数 {{ total_entries }}</li>
    <li class="active">房间在线个数 {{ online_room_num }}</li>
</ol>

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
    是否热门：{{ room.hot_text }}<br/>
    房间类型：{{ room.types_text }}<br/>
    房间频道号：{{ room.channel_name }}
{% endmacro %}

{% macro room_status_info(room, boom_config) %}
    房间: {{ room.status_text }}|房主:{{ room.online_status_text }}|用户:{{ room.user_type_text }}<br/>
    最后活跃时间: {{ room.last_at_text }}<br/>
    公频聊天状态: {{ room.chat_text }}<br/>
    是否加锁: {{ room.lock_text }}<br/>
    是否热门: {{ room.hot_text }}|是否置顶: {{ room.top_text }}|是否最新: {{ room.new_text }}<br/>
    协议: {{ intval(room.user_agreement_num) }}<br/>
    当前爆礼物值: {{ room.getCurrentBoomGiftValue() }}<br/>
    当天爆礼次数: {{ room.getBoomNum() }}<br/>
    当次引爆爆者ID: {{ room.getBoomUserId() }}<br/>
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
    {% if isAllowed('rooms','types') %}
        <a href="/admin/rooms/types?id={{ room.id }}" class="modal_action">类型配置</a></br>
    {% endif %}
    {% if isAllowed('rooms','shield_config') %}
        <a href="/admin/rooms/shield_config?id={{ room.id }}" class="modal_action">地区屏蔽配置</a></br>
    {% endif %}
    {% if isAllowed('rooms','forbidden_to_hot') %}
        <a href="/admin/rooms/forbidden_to_hot?id={{ room.id }}" class="modal_action">禁止上热门</a></br>
    {% endif %}
    {% if isAllowed('rooms','edit') %}
        <a href="/admin/rooms/edit?id={{ room.id }}" class="modal_action">编辑</a>
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
            是否热门：${ room.hot_text }<br/>
            房间类型：${ room.types_text }
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
            {% if isAllowed('rooms','detail') %}
                <a href="/admin/rooms/detail?id=${ room.id }">详细</a></br>
            {% endif %}
            {% if isAllowed('rooms','add_user_agreement') %}
                <a href="/admin/rooms/add_user_agreement?id=${ room.id }" class="modal_action">添加协议</a></br>
            {% endif %}
            {% if isAllowed('rooms','delete_user_agreement') %}
                <a href="/admin/rooms/delete_user_agreement?id=${ room.id }" id="delete_user_agreement">清除协议</a></br>
            {% endif %}
            {% if isAllowed('rooms','types') %}
                <a href="/admin/rooms/types?id=${ room.id }" class="modal_action">类型配置</a></br>
            {% endif %}
            {% if isAllowed('rooms','shield_config') %}
                <a href="/admin/rooms/shield_config?id=${ room.id }" class="modal_action">地区屏蔽配置</a></br>
            {% endif %}
            {% if isAllowed('rooms','forbidden_to_hot') %}
                <a href="/admin/rooms/forbidden_to_hot?id=${ room.id }" class="modal_action">禁止上热门</a></br>
            {% endif %}
            {% if isAllowed('rooms','edit') %}
                <a href="/admin/rooms/edit?id=${ room.id }" class="modal_action">编辑</a>
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