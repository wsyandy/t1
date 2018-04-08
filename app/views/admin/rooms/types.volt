{{ js('/js/common.js') }}
{% set f = simple_form(c('/admin/rooms/update_types?id='~id),[ 'method': 'post' , 'class': 'ajax_model_form','data-model':'types']) %}

<a class="batch_select" data-select_option="all" data-target="select_partner">全选</a>
<a class="batch_select" data-select_option="reverse" data-target="select_partner"> 反选</a>

<div class="form-group string optional" style="width: 980px !important;">
    <label class="string optional control-label">支持的类型</label>
    <div id="select_partner">
        {% for index, type in types %}
            <input type="checkbox" name="types[]"
                   class="{% if '*' == index %}select_all{% else %}select_single{% endif %}" value="{{ index }}"
                   {% if in_array(index, all_select_types) %}checked="checked" {% endif %}/>{{ type }}
        {% endfor %}
    </div>
</div>

{{ f.submit('保存') }}
{{ f.end }}