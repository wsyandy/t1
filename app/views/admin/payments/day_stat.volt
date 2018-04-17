<form action="/admin/payments/day_stat" method="get" class="search_form" autocomplete="off" id="search_form">

    <label for="stat_at_eq">时间</label>
    <input name="stat_at" type="text" id="stat_at_eq" class="form_datetime" value="{{ stat_at }}"/>

    <button type="submit" class="ui button">搜索</button>
</form>

<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>支付方式</th>
        <th>金额</th>
        <th>占比</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for payment_type, amount in stats %}
        {% set rate = 0 %}
        {% if stats['total'] != 0 %}
            {% set rate = intval(amount*100/stats['total'])/100 %}
        {% endif %}
        <tr id="{{ payment_type }}" class="row_line">
            <td>{{ payment_type }}</td>
            <td>{{ amount }}</td>
            <td>{{ rate }}</td>
        </tr>
    {% endfor %}
    </tbody>
</table>

<script type="text/javascript">
    $(".form_datetime").datetimepicker({
        language: "zh-CN",
        format: 'yyyy-mm-dd',
        autoclose: 1,
        todayBtn: 1,
        todayHighlight: 1,
        startView: 2,
        minView: 2
    });
</script>