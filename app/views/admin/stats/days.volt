<form method="get" action="/admin/stats/days" name="search_form" autocomplete="off">
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

        {% for stat in stats %}
        {% for index, value in stat.data | json_decode %}
        $("#{{ index }}_{{ stat.stat_at_date }}").html({{ value }});
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


        var register_num = parseFloat($("#register_num .total").eq(1).html());
        var device_active_num = parseFloat($("#device_active_num .total").eq(1).html());
        var subscribe_num = parseFloat($("#subscribe_num .total").eq(1).html());
        if (subscribe_num + device_active_num > 0) {
            $("#register_rate .total").html(Math.ceil(register_num * 100 / (subscribe_num + device_active_num)));
        }

        var create_order_user = parseFloat($("#create_order_user .total").eq(1).html());
        var create_order_num = parseFloat($("#create_order_num .total").eq(1).html());
        $("#create_order_average .total").html(Math.ceil(create_order_num * 100 / create_order_user) / 100);

        var create_order_product_user = parseFloat($("#create_order_product_user .total").eq(1).html());
        $("#create_order_product_average .total").html(Math.ceil(create_order_product_user * 100 / create_order_user) / 100);

        if (register_num > 0) {
            $("#new_create_order_rate .total").html(Math.ceil(new_create_order_user * 100 / register_num));
        }

        var active_user_num = parseFloat($("#active_user_num .total").eq(1).html());
        if (active_user_num) {
            $("#create_order_rate .total").html(Math.ceil(create_order_user * 100 / active_user_num));
        }

    });
</script>