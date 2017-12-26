<a href="/admin/sms_histories/add_black_list" class="modal_action">新建</a>

<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>黑名单</th>
        <th>删除</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for index, mobile in mobiles %}
        <tr id="{{ mobile }}" class="row_line">
            <td>{{ mobile }}</td>
            <td><a class="delete_action" href="/admin/sms_histories/delete_black_mobile?mobile={{ mobile }}">删除</a></td>
        </tr>
    {% endfor %}
    </tbody>
</table>