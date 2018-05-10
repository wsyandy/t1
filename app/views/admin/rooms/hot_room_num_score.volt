<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>时间</th>
        <th>分值</th>
    </tr>
    </thead>
    <tbody id="stat_list">

    {% for time, score in scores %}
        <tr class="row_line">
            <td>{{ time }}</td>
            <td>{{ score }}</td>
        </tr>
    {% endfor %}
    </tbody>
</table>