{% set f = simple_form(c('/admin/rooms/shield_config/',room_id), [ 'method': 'post' , 'class': 'ajax_model_form', 'data-model':'room']) %}

<div class="form-group string optional" style="width: 980px !important;">
    <label class="string optional control-label">城市选择</label>
    <label class="string optional control-label" id="all_select" style="color: #2b67f1">全选</label>
    <label class="string optional control-label" id="cancle_select" style="color: #2b67f1">取消全选</label>
    <div>
        {% for index, province in provinces %}
            <input type="checkbox" class="city_check province_check" data-id="{{ province['id'] }}" name="province_ids[]" value="{{ province['id'] }}"/>
            <b>{{ province['name'] }}</b>
            {% for city in province['child'] %}
                <input type="checkbox" name="city_ids[]" class="city_check city_check{{ province['id'] }}"
                       value="{{ city['id'] }}" {% if in_array(city['id'], city_ids_list) %} checked="checked" {% endif %}/>
                {{ city['name'] }}
            {% endfor %}
            <br><br>
        {% endfor %}
    </div>
</div>

<div class="error_reason" style="color: red"></div>
{{ f.submit('提交') }}

{{ f.end }}

<script>
    $("#cancle_select").click(function () {
        $(".city_check").removeAttr('checked');
    })

    $("#all_select").click(function () {
        $(".city_check").prop('checked', true);
    });

    $(".province_check").click(function () {
        var id = $(this).data('id');
        if ($(this).is(":checked")) {
            $(".city_check" + id).prop('checked', true);
        } else {
            $(".city_check" + id).prop('checked', false);
        }
    })

    $.each($(".province_check"), function (index, item) {
        var id = $(this).data('id');
        //console.log(id);
        if ($(".city_check" + id).is(":checked")) {
            $(this).prop('checked', true);
        }
    })

    $(".modal-content").css({'width': '1000px', 'margin-right': '70%'});
    $(".modal-dialog").css({'margin-right': '40%'});

</script>