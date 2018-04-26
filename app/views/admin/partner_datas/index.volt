<a class="modal_action" href="/admin/partner_datas/new">新建</a>

<form method="get" action="/admin/partner_datas/index" name="search_form" autocomplete="off">

    <label for="start_at">日期</label>
    <input type="text" name="start_at" class="form_datetime" id="start_at" value="{{ start_at }}" size="16">

    <button class="ui button" type="submit">查询</button>
</form>

<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>渠道</th>
        <th>产品渠道</th>

        {% for key, text in stat_fields %}
            <th>{{ text }}</th>
        {% endfor %}
        <th>编辑</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for partner_data in partner_datas %}
        <tr id="{{ partner_data.id }}" class="row_line">
            <td>{{ partner_data.partner.name }}</td>
            <td>{{ partner_data.product_channel.name }}</td>

            {% for stat_field,text  in stat_fields %}
                <td id="{{ partner_data.id }}_{{ stat_field }}"></td>
            {% endfor %}
            <td><a href="/admin/partner_datas/edit/{{ partner_data.id }}" class="modal_action">编辑</a></td>
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

        {% for partner_data in partner_datas %}
        {% for index_key, value in partner_data.data | json_decode %}
        $("#{{ partner_data.id }}_{{ index_key }}").html({{ value }});
        {% endfor %}
        {% endfor %}

    });
</script>