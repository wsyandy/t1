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
        {{ options(gifts, gift_id, 'id', 'name') }}
    </select>

    <label for="stat_at">时间</label>
    <input name="stat_at" type="text" id="stat_at" class="form_datetime" value="{{ stat_at }}"/>

    <button class="ui button" type="submit">搜索</button>
</form>


<table class="table table-striped table-condensed">
    <thead>
    <tr>
        {% for key, text in stat_fields %}
            <th>{{ text }}</th>
        {% endfor %}
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for gift_stat in gift_stats %}
        <tr id="{{ gift_stat.id }}" class="row_line">
            {% for stat_field, text  in stat_fields %}
                <td id="{{ stat_field }}_{{ gift_stat.id }}"></td>
            {% endfor %}
        </tr>
    {% endfor %}
    </tbody>
</table>


<script type="text/javascript">

    $(function () {
        $('.selectpicker').selectpicker();
        {% for gift_stat in gift_stats %}
        {% for index, value in gift_stat.data | json_decode %}
        $("#{{ index }}_{{ gift_stat.id }}").html({{ value }});
        {% endfor %}
        {% endfor %}
    });

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
