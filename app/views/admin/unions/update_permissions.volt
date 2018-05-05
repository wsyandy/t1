{% set f = simple_form(c('/admin/unions/update_permissions/', union.id), union,[ 'method': 'post' , 'class': 'ajax_model_form']) %}

<div class="form-group string optional" style="width: 980px !important;">
    <label class="string optional control-label">权限</label>
    <div>
        {% for index, permission in permissions %}
            <input type="checkbox" name="permissions[]" class="{% if '' == index %}select_all{% else %}select_single{% endif %}" value="{{ index }}"
                   {% if in_array(index, all_select_permissions) %}checked="checked" {% endif %}/>{{ permission }}
        {% endfor %}
    </div>
</div>

<div class="error_reason" style="color: red"></div>
{{ f.submit('保存') }}
{{ f.end }}
