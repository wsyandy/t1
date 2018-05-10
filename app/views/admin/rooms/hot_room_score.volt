<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>时间</th>
        <th>分值</th>
    </tr>
    </thead>
    <tbody id="stat_list">

    {% for score, time in scores %}
        <tr class="row_line">
            <td>{{ date("Y-m-d H:i:s", time) }}</td>
            <td>{{ score }}</td>
        </tr>
    {% endfor %}
    </tbody>
</table>