<form action="/admin/rooms/game_white_list" method="post" enctype="multipart/form-data">
    <a href="/admin/rooms/add_game_white_list" class="modal_action">新建</a>

    <label for="id">房间ID</label>
    <input type="text" name="id" id="id"/>
    <input type="submit" name="submit" value="搜索"/>
</form>

<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>白名单</th>
        <th>删除</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for index, room_id in room_id_list %}
        <tr id="{{ room_id }}" class="row_line">
            <td>{{ room_id }}</td>
            <td><a class="delete_action" href="/admin/rooms/delete_game_white_list?id={{ room_id }}">删除</a></td>
        </tr>
    {% endfor %}
    </tbody>
</table>