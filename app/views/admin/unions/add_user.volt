{% set f = simple_form('/admin/unions/add_user', ['class':'ajax_model_form', 'method':'post','data-model': 'union']) %}

{{ f.input('user_id',[ 'label':'用户id']) }}
<input type="hidden" name="id" value="{{ id }}">
{{ f.submit('保存') }}

<div style="color: red" class="error_reason"></div>
{{ f.end }}