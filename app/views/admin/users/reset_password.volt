{% set f = simple_form(c('/admin/users/reset_password?id=', id),['method':'post', 'class':'ajax_model_form','data-model':'user']) %}
{{ f.input('password',['label':'密码']) }}
{{ f.submit('保存') }}
{{ f.end }}