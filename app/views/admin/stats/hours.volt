<form method="get" action="/admin/stats/hours" name="search_form" autocomplete="off">
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
        推广渠道
    </label>
    <select name="partner_id" id="partner_id" class="selectpicker" data-live-search="true">
        {{ options(partners, partner_id, 'id', 'name') }}
    </select>

    <label for="stat_at">时间</label>
    <input type="text" name="stat_at" class="form_datetime" id="stat_at" value="{{ stat_at }}" size="16">

    <button class="ui button" type="submit">搜索</button>
</form>

<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>项目/小时</th>
        {% for hour in hour_array %}
            <th>{{ hour }}</th>
        {% endfor %}
        <th>项目/小时</th>
    </tr>
    </thead>
    <tbody id="stat_list">
    {% for index, text in data_array %}
        <tr id="{{ index }}">
            <td>{{ text }}</td>
            {% for hour in hour_array %}
                <td id="{{ index }}_{{ hour }}"></td>
            {% endfor %}
            <td>{{ text }}</td>
        </tr>
    {% endfor %}
    </tbody>
</table>

<script type="text/javascript">


    $(function () {
        $(".form_datetime").datetimepicker({
            language: "zh-CN",
            format: 'yyyy-mm-dd',
            autoclose: 1,
            todayBtn: 1,
            todayHighlight: 1,
            startView: 2,
            minView: 2
        });

        {% for stat in stats %}
        {% for index, value in stat.data|json_decode %}
        $("#{{ index }}_{{ stat.stat_at_hour }}").html({{ value }});
        {% endfor %}
        {% endfor %}
    });
</script>