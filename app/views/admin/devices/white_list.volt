<form action="/admin/devices/white_list" method="post" enctype="multipart/form-data">
    <a href="/admin/devices/add_white_list" class="modal_action">新建</a>

    <label for="dno">设备号</label>
    <input type="text" name="dno" id="dno"/>
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
    {% for index, dno in dno_list %}
        <tr id="{{ dno }}" class="row_line">
            <td>{{ dno }}</td>
            <td><a class="delete_action" href="/admin/devices/delete_white_list?dno={{ dno }}">删除</a></td>
        </tr>
    {% endfor %}
    </tbody>
</table>