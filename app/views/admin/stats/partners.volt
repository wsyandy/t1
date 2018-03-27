<form method="get" action="/admin/stats/partners" name="search_form" autocomplete="off">

    <label for="product_channel_id">
        产品
    </label>
    <select name="product_channel_id" id="product_channel_id" class="selectpicker" data-live-search="true">
        {{ options(product_channels, product_channel_id, 'id', 'name') }}
    </select>

    <label for="stat_at">开始时间</label>
    <input type="text" name="start_stat_at" class="form_datetime" id="stat_at" value="{{ start_stat_at }}" size="16">
    <label for="stat_at">结束时间</label>
    <input type="text" name="end_stat_at" class="form_datetime" id="stat_at" value="{{ end_stat_at }}" size="16">

    <button class="ui button" type="submit">搜索</button>
</form>

<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>渠道</th>
        <th>fr</th>
        {% for key, text in stat_fields %}
            <th>{{ text }}</th>
        {% endfor %}
        <th>渠道</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for partner in partners %}
        <tr id="{{ partner.id }}" class="row_line">
            <td>{{ partner.name }}</td>
            <td>{{ partner.fr }}</td>
            {% for stat_field,text  in stat_fields %}
                <td id="{{ partner.id }}_{{ stat_field }}"></td>
            {% endfor %}
            <td>{{ partner.name }}</td>
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


<script type="text/javascript">

    $(function () {
        {% for stat in stats %}
        {% for index, value in stat.data | json_decode %}
        $("#{{ stat.partner_id }}_{{ index }}").html({{ value }});
        {% endfor %}
        {% endfor %}

    });
</script>
