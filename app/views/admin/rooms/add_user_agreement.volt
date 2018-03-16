{% set f = simple_form('/admin/rooms/add_user_agreement', ['class': 'ajax_model_form', 'data-model': 'room']) %}

<input type="hidden" name="id" value="{{ room.id }}"/>
{{ f.input('user_agreement_num', ['label': '人数']) }}
<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}