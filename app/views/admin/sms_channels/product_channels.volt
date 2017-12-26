{% set f = simple_form('/admin/sms_channels/update_product_channels', ['class': 'ajax_model_form','data-model':'sms_channel']) %}

<div class="form-group string optional">
    <label class="string optional control-label">产品渠道(默认全部)</label>
    <div>
        <input name="id" value="{{sms_channel.id}}" type="hidden"/>
        {% for product_channel in all_product_channels %}
            <input type="checkbox" name="product_channel_ids[]" value="{{product_channel.id}}"{% if product_channel.id in product_channel_ids %} checked="checked" {% endif %}/>
            {{product_channel.name}}<br>
        {% endfor %}
    </div>
</div>

{{ f.submit('提交') }}

{{ f.end }}