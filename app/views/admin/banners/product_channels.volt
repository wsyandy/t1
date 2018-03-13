{{ js('/js/common.js') }}
{% set f = simple_form('/admin/banners/update_product_channels', banner, ['method': 'post', 'class': 'ajax_model_form']) %}

<input type="hidden" name="id" value="{{ id }}">
<a class="batch_select" data-select_option="all" data-target="select_product_channel">全选</a>
<a class="batch_select" data-select_option="reverse" data-target="select_product_channel"> 反选</a>

<div class="form-group string optional">
    <label class="string optional control-label">产品渠道</label>

    <div id="select_product_channel">
        {% for k, product_channel in all_product_channels %}
            <input type="checkbox" name="product_channel_ids[]" value="{{product_channel.id}}"{% if in_array(product_channel.id, product_channel_banner_ids) %} checked="checked" {% endif %}/>
            {{product_channel.name}}{% if (k + 1) % 5 == 0 %}<br>{% endif %}
        {% endfor %}
    </div>
</div>

{{ f.submit('提交') }}

{{ f.end }}