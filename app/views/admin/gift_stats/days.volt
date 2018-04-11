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

{%- macro gift_num_link(gift_stat) %}
    <div id="gift_num_{{ gift_stat.id }}"></div>
{%- endmacro %}
{%- macro gift_user_link(gift_stat) %}
    <div id="gift_user_{{ gift_stat.id }}"></div>
{%- endmacro %}
{%- macro gift_total_link(gift_stat) %}
    <div id="gift_total_{{ gift_stat.id }}"></div>
{%- endmacro %}

{{ simple_table(gift_stats,['礼物':'gift_name','礼物赠送总次数':'gift_num_link','礼物赠送总人数':'gift_user_link','礼物赠送总个数':'gift_total_link']) }}

<script type="text/javascript">

    $(function () {
        $('.selectpicker').selectpicker();
        {% for gift_stat in gift_stats %}
        {% for index, value in gift_stat.data | json_decode %}
        $("#{{ index }}_{{ gift_stat.id }}").html({{ value }});
        {% endfor %}
        {% endfor %}
    });

</script>

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