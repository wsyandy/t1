{{ js('/js/common.js') }}
{% set f = simple_form(c('/admin/weixin_template_messages/update_support_platforms/', weixin_template_message_id), ['class': 'ajax_model_form','data-model':'weixin_template_message']) %}

<a class="batch_select" data-select_option="all" data-target="select_province">全选</a>
<a class="batch_select" data-select_option="reverse" data-target="select_province"> 反选</a>
<div class="form-group string optional">
    <div id="select_province">
        {% for k, platform in platforms %}
            <input type="checkbox" name="platforms[]"
                   value="{{ k }}" {% if in_array(k, support_platforms) %} checked="true"{% endif %}/>
            {{ platform }}
        {% endfor %}
    </div>
</div>

{{ f.submit('提交') }}

{{ f.end }}