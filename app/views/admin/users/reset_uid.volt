{% set f = simple_form(c('/admin/users/reset_uid?id=', id),['method':'post', 'class':'ajax_model_form','data-model':'user']) %}
{{ f.input('uid',['label':'新的用户id']) }}
{{ f.submit('保存') }}
<div class="error_reason" style="color: red"></div>
{{ f.end }}