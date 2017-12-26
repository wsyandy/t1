<form method="get" action="/admin/sms_histories/login_hour_stat" autocomplete="off">
    <label for="product_channel_id">
        产品渠道
    </label>
    <select name="product_channel_id" id="product_channel_id" class="selectpicker" data-live-search="true">
        {{ options(product_channels, product_channel_id, 'id', 'name') }}
    </select>

    <label for="day">时间</label>
    <input type="text" name="day" class="form_datetime" id="day" value="{{ day }}" size="16">

    <button class="ui button" type="submit">搜索</button>
</form>

<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>时间</th>
        <th>发送人数</th>
        <th>成功人数</th>
        <th>验证人数</th>
        <th>验证率</th>
        <th>成本(元)</th>
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
        </tr>
    {% endfor %}
    </tbody>
</table>


<script type="text/javascript">

    $(function () {
        $('.selectpicker').selectpicker();

        $(".form_datetime").datetimepicker({
            language: "zh-CN",
            format: 'yyyy-mm-dd',
            autoclose: 1,
            todayBtn: 1,
            todayHighlight: 1,
            startView: 2,
            minView: 2
        });

    });

</script>