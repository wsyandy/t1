{% set f = simple_form('/admin/rooms/forbidden_to_hot',['class': 'ajax_model_form', 'method':'POST', 'data-model': 'room']) %}

<input type="hidden" name="id" value="{{ room.id }}"/>
{{ f.input('forbidden_reason', ['label': '禁止原因']) }}
{{ f.input('forbidden_time', ['label': '禁止时长(单位小时)']) }}
<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}