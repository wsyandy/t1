{% set f = simple_form(c('/admin/push_messages/update_platforms/',push_message.id), push_message,[ 'method': 'post' , 'class': 'ajax_model_form']) %}

<div class="form-group string optional" style="width: 980px !important;">
    <label class="string optional control-label">支持的平台</label>
    <div>
        {% for index, platform in platforms %}
            <input type="checkbox" name="platforms[]" class="{% if '' == index %}select_all{% else %}select_single{% endif %}" value="{{ index }}"
                   {% if in_array(index, all_select_platforms) %}checked="checked" {% endif %}/>{{ platform }}
        {% endfor %}
    </div>
</div>

<div class="error_reason" style="color: red"></div>
{{ f.submit('保存') }}
{{ f.end }}
