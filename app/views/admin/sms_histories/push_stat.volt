<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>时间</th>
        <th>发送人数</th>
        <th>成功人数</th>
        <th>(唤醒/验证)人数</th>
        <th>(唤醒/验证)率</th>
        <th>成本(元)</th>
        <th>申请贷款人数</th>
        <th>唤醒申请率</th>
        <th>申请贷款个数</th>
        <th>人均申请贷款方个数</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for day, result in results %}
        {% set rate = 0 %}
        {% if result[1] != 0 %}
            {% set rate = intval(result[2]*100/result[1])/100 %}
        {% endif %}
        <tr id="{{ day }}" class="row_line">
            <td>{{ day }}</td>
            <td>{{ result[0] }}</td>
            <td>{{ result[1] }}</td>
            <td>{{ result[2] }}</td>
            <td>{{ rate }}</td>
            <td>{{ intval(result[0] * 0.05) }}</td>
            <td>{{ result[3] }}</td>
            <td>{{ result[4] }}</td>
            <td>{{ result[5] }}</td>
            <td>{{ result[6] }}</td>
        </tr>
    {% endfor %}
    </tbody>
</table>