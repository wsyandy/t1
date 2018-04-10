<form method="get" action="/admin/gift_stats/days" name="search_form" autocomplete="off">
    <label for="product_channel_id">
        产品
    </label>
    <select name="product_channel_id" id="product_channel_id" class="selectpicker" data-live-search="true">
        {{ options(product_channels, product_channel_id, 'id', 'name') }}
    </select>

    <label for="gift_id">
        礼物
    </label>
    <select name="gift_id" id="gift_id" class="selectpicker" data-live-search="true">
        {{ options(gift_ids, gift_id, 'id', 'name') }}
    </select>


    <label for="year">年份</label>
    <select name="year" id="year">
        {{ options(year_array,year) }}
    </select>
    <label for="month">月份</label>
    <select name="month" id="month">
        {{ options(Stats.MONTH,month) }}
    </select>


    <button class="ui button" type="submit">搜索</button>
</form>

<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>项目/日期</th>
        <th>累计</th>
        {% for index,day in day_array %}
            <th>{{ index }}</th>
        {% endfor %}
        <th>累计</th>
        <th>项目/日期</th>
    </tr>
    </thead>
    <tbody id="stat_list">

    {% for index, text in data_array %}
        <tr id="{{ index }}" class="row_line">
            <td>{{ text }}</td>
            <td class="total"></td>
            {% for day in day_array %}
                <td id="{{ index }}_{{ day }}"></td>
            {% endfor %}
            <td class="total"></td>
            <td>{{ text }}</td>
        </tr>
    {% endfor %}
    </tbody>
</table>


<script type="text/javascript">

    $(function () {

        $('.selectpicker').selectpicker();

        {% for gift_stat in gift_stats %}
        {% for index, value in gift_stat.data | json_decode %}
        $("#{{ index }}_{{ gift_stat.stat_at_date }}").html({{ value }});
        {% endfor %}
        {% endfor %}

        $(".row_line").each(function () {
            var total = 0;
            $(this).find("td[id]").each(function (index, element) {
                var value = $(this).html();
                if ("" !== value) {
                    total += parseFloat(value);
                }
            });
            var text = $(this).find("td:eq(0)").html();

            if (text.indexOf("%") < 0) {
                total = Math.ceil(total);
                $(this).find(".total").html(total);
            }

        });

    });
</script>