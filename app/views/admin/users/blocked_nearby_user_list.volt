<form action="/admin/users/blocked_nearby_user_list" method="post" enctype="multipart/form-data">
    <a href="/admin/users/add_blocked_nearby_user" class="modal_action">新建</a>

    <label for="user_id">用户id</label>
    <input type="text" name="user_id" id="user_id"/>
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
    {% for index, user_id in user_id_list %}
        <tr id="{{ user_id }}" class="row_line">
            <td>{{ user_id }}</td>
            <td><a class="delete_action" href="/admin/users/delete_blocked_nearby_user?user_id={{ user_id }}">删除</a></td>
        </tr>
    {% endfor %}
    </tbody>
</table>