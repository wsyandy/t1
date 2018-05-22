<form action="/admin/pk_histories" method="get" class="search_form" autocomplete="off" id="search_form">

    <label for="id_eq">ID</label>
    <input name="pk_histories[id_eq]" type="text" id="id_eq"/>

    <label for="user_id_eq">用户ID</label>
    <input name="pk_histories[user_id_eq]" type="text" id="user_id_eq"/>

    <label for="status">状态</label>
    <select name="pk_histories[status_eq]" id="status_eq">
        {{ options(PkHistories.STATUS) }}
    </select>

    <button type="submit" class="ui button">搜索</button>
</form>

{{ simple_table(pk_histories,['id': 'id','房间ID':'room_id','用户ID':'user_id','左边用户ID':'left_pk_user_id','右边用户ID':'right_pk_user_id','左边用户分数':'left_pk_user_score','右边用户分数':'right_pk_user_score','PK类型':'pk_type','状态':'status','创建时间':'created_at_text']) }}