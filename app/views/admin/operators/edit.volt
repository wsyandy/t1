{% set f = simple_form(['admin', operator],['method':'post', 'class':'ajax_model_form']) %}
{{ f.password('password',['label': '密码']) }}
{{ f.select('status', ['label': '状态', 'collection': Operators.STATUS]) }}
{{ f.select('role', ['label': '角色', 'collection': Operators.ROLE]) }}

{{ f.submit('保存') }}
<div style="color: red" class="error_reason"></div>
{{ f.end }}