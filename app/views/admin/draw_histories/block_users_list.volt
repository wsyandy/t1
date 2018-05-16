<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>黑名单</th>
        <th>删除</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for index, id in block_users_list %}
        <tr id="{{ id }}" class="row_line">
            <td>{{ id }}</td>
            <td><a class="delete_action" href="/admin/draw_histories/delete_block_user?id={{ id }}">删除</a></td>
        </tr>
    {% endfor %}
    </tbody>
</table>