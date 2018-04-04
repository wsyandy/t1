{{ js('/js/common.js') }}
{% set f = simple_form(c('/admin/activities/update_product_channel_ids?id='~id), [ 'method': 'post' , 'class': 'ajax_model_form', 'data-model':'product_channels']) %}

<a class="batch_select" data-select_option="all" data-target="select_partner">全选</a>
<a class="batch_select" data-select_option="reverse" data-target="select_partner"> 反选</a>

<div class="form-group string optional" style="width: 980px !important;">
    <label class="string optional control-label">渠道选择(不选代表支持全部)</label>
    <div id="select_partner">
        {% for index, product_channel in product_channels %}
            <input type="checkbox" name="product_channel_ids[]" value="{{ product_channel.id }}"
                   {% if in_array(product_channel.id, select_product_channel_ids) %}checked="checked"{% endif %}/>{{ product_channel.name }}
            {% if (index + 1) % 4 == 0 %}<br>{% endif %}
        {% endfor %}
    </div>
</div>
<div class="error_reason" style="color: red"></div>
{{ f.submit('提交') }}

{{ f.end }}
