<form method="get" action="/admin/province_stats/days" name="search_form" autocomplete="off">
    <label for="product_channel_id">
        产品
    </label>
    <select name="product_channel_id" id="product_channel_id" class="selectpicker" data-live-search="true">
        {{ options(product_channels, product_channel_id, 'id', 'name') }}
    </select>

    <label for="platform">
        平台
    </label>
    <select name="platform" id="platform">
        {{ options(platforms, platform) }}
    </select>

    <label for="partner_id">
        <select name="partner_id" id="partner_id">
            {{ options(partners,partner_id,'id','name') }}
        </select>
    </label>

    <label for="start_at">日期</label>
    <input type="text" name="start_at" class="form_datetime" id="start_at" value="{{ start_at }}" size="16">

    <button class="ui button" type="submit">查询</button>
</form>

<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>省份</th>
        {% for key, text in stat_fields %}
            <th>{{ text }}</th>
        {% endfor %}
        <th>省份</th>
    </tr>
    </thead>

    <tbody id="stat_list">

    {% for province_stat in province_stats %}
        <tr id="{{ province_stat.id }}" class="row_line">
            <td>{{ province_stat.province_name }}</td>
            {% for stat_field,text  in stat_fields %}
                <td id="{{ province_stat.id }}_{{ stat_field }}"></td>
            {% endfor %}
            <td>{{ province_stat.province_name }}</td>
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

        $('.selectpicker').selectpicker();

        {% for province_stat in province_stats %}
        {% for index_key, value in province_stat.data | json_decode %}
        $("#{{ province_stat.id }}_{{ index_key }}").html({{ value }});
        {% endfor %}
        {% endfor %}

    });
</script>
