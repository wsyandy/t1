<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>时间</th>
        <th>收益</th>
        <th>明细</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for day, result in results %}
        <tr id="{{ day }}" class="row_line">
            <td>{{ day }}</td>
            <td>{{ result[0] }}钻石</td>
            <td><a href="/admin/gift_orders?gift_order[room_id_eq]={{ room_id }}&
                    start_at={{ result[1] }}&end_at={{ result[2] }}">明细</a></td>
        </tr>
    {% endfor %}
    </tbody>
</table>


<script type="text/javascript">

    $(function () {
        $('.selectpicker').selectpicker();
    });

</script>