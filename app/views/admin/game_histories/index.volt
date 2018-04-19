<form action="/admin/game_histories" method="get" class="search_form" autocomplete="off" id="search_form">

    <label for="id_eq">ID</label>
    <input name="game_history[id_eq]" type="text" id="id_eq"/>

    <label for="user_id_eq">用户ID</label>
    <input name="game_history[user_id_eq]" type="text" id="user_id_eq"/>

    <label for="status">状态</label>
    <select name="game_history[status_eq]" id="status_eq">
        {{ options(GameHistories.STATUS) }}
    </select>

    <button type="submit" class="ui button">搜索</button>
</form>

{{ simple_table(game_histories,['id': 'id','房间ID':'room_id','游戏发起者':'user_nickname','创建时间':'created_at_text','开始数据':'start_data','结束数据':'end_data','游戏状态':'status_text']) }}

