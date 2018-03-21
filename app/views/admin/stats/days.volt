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
        var total_active_num = parseFloat($("#total_active_num .total").eq(1).html());
        if (total_active_num > 0) {
            $("#register_rate .total").html(Math.ceil(register_num * 100 / total_active_num));
        }

        var create_order_user = parseFloat($("#create_order_user .total").eq(1).html());
        var create_order_num = parseFloat($("#create_order_num .total").eq(1).html());
        if (create_order_user > 0) {
            $("#create_order_average .total").html(Math.ceil(create_order_num * 100 / create_order_user) / 100);
        }
        var new_create_order_user = parseFloat($("#new_create_order_user .total").eq(1).html());
        var new_create_order_num = parseFloat($("#new_create_order_num .total").eq(1).html());
        if (new_create_order_user > 0) {
            $("#new_create_order_average .total").html(Math.ceil(new_create_order_num * 100 / new_create_order_user) / 100);
        }

        var create_payment_user = parseFloat($("#create_payment_user .total").eq(1).html());
        var create_payment_num = parseFloat($("#create_payment_num .total").eq(1).html());
        if (create_payment_user > 0) {
            $("#create_payment_average .total").html(Math.ceil(create_payment_num * 100 / create_payment_user) / 100);
        }
        var new_create_payment_user = parseFloat($("#new_create_payment_user .total").eq(1).html());
        var new_create_payment_num = parseFloat($("#new_create_payment_num .total").eq(1).html());
        if (new_create_payment_user > 0) {
            $("#new_create_payment_average .total").html(Math.ceil(new_create_payment_num * 100 / new_create_payment_user) / 100);
        }

        var payment_success_user = parseFloat($("#payment_success_user .total").eq(1).html());
        var payment_success_num = parseFloat($("#payment_success_num .total").eq(1).html());
        if (payment_success_user > 0) {
            $("#payment_success_average .total").html(Math.ceil(payment_success_num * 100 / payment_success_user) / 100);
        }
        var new_payment_success_user = parseFloat($("#new_payment_success_user .total").eq(1).html());
        var new_payment_success_num = parseFloat($("#new_payment_success_num .total").eq(1).html());
        if (new_payment_success_user > 0) {
            $("#new_payment_success_average .total").html(Math.ceil(new_payment_success_num * 100 / new_payment_success_user) / 100);
        }

        if (create_order_user > 0) {
            $("#order_payment_rate .total").html(Math.ceil(create_payment_user * 100 / create_order_user));
        }
        if (new_create_order_user > 0) {
            $("#new_order_payment_rate .total").html(Math.ceil(new_create_payment_user * 100 / new_create_order_user));
        }
        if (create_payment_user > 0) {
            $("#payment_success_rate .total").html(Math.ceil(payment_success_user * 100 / create_payment_user));
        }
        if (new_create_payment_user > 0) {
            $("#new_payment_success_rate .total").html(Math.ceil(new_payment_success_user * 100 / new_create_payment_user));
        }

        var payment_success_total = parseFloat($("#payment_success_total .total").eq(1).html());

        if (payment_success_user > 0) {
            $("#paid_arpu .total").html(Math.ceil(payment_success_total * 100 / payment_success_user) / 100);
        }

        var new_payment_success_total = parseFloat($("#new_payment_success_total .total").eq(1).html());

        if (new_payment_success_user > 0) {
            $("#new_paid_arpu .total").html(Math.ceil(new_payment_success_total * 100 / new_payment_success_user) / 100);
        }


        var active_register_user_num = parseFloat($("#active_register_user_num .total").eq(1).html());

        if (active_register_user_num > 0) {
            $("#arpu .total").html(Math.ceil(payment_success_total * 100 / active_register_user_num) / 100);
        }

        if (register_num > 0) {
            $("#new_arpu .total").html(Math.ceil(new_payment_success_total * 100 / register_num) / 100);
        }

        var diamond_recharge_user = parseFloat($("#diamond_recharge_user .total").eq(1).html());
        var diamond_recharge_num = parseFloat($("#diamond_recharge_num .total").eq(1).html());
        var diamond_recharge_total = parseFloat($("#diamond_recharge_total .total").eq(1).html());

        if (diamond_recharge_user > 0) {
            $("#diamond_recharge_num_average .total").html(Math.ceil(diamond_recharge_num * 100 / diamond_recharge_user) / 100);
            $("#diamond_recharge_user_average .total").html(Math.ceil(diamond_recharge_total * 100 / diamond_recharge_user) / 100);
        }

        var diamond_cost_user = parseFloat($("#diamond_cost_user .total").eq(1).html());
        var diamond_cost_num = parseFloat($("#diamond_cost_num .total").eq(1).html());
        var diamond_cost_total = parseFloat($("#diamond_cost_total .total").eq(1).html());

        if (diamond_cost_user > 0) {
            $("#diamond_cost_num_average .total").html(Math.ceil(diamond_cost_num * 100 / diamond_cost_user) / 100);
            $("#diamond_recharge_user_average .total").html(Math.ceil(diamond_cost_total * 100 / diamond_cost_user) / 100);
        }

        $("#diamond_recharge_balance .total").html(Math.ceil(diamond_recharge_total - diamond_cost_total));

    });
</script>