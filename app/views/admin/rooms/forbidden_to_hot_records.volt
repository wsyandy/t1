<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>操作时间</th>
        <th>禁止记录</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for record , time in records %}
        <tr>
            <td>{{ date("Y-m-d H:i:s", time) }}</td>
            <td>{{ record }}</td>
        </tr>
    {% endfor %}
    </tbody>
</table>