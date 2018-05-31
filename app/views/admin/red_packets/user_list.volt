<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>用户ID</th>
        <th>钻石</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for id, diamond in result_datas %}
        <tr id="{{ id }}" class="row_line">
            <td>{{ id }}</td>
            <td>{{ diamond }}</td>
        </tr>
    {% endfor %}
    </tbody>
</table>