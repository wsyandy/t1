{% set f = simple_form('/admin/broadcasts/compile_room?room_id='~room_id,room,['method':'POST','class':'ajax_model_form','data_modal':'room']) %}
{{ f.input('name',['label':'房间名']) }}
{{ f.input('topic',['label':'话题']) }}
<div class="error_reason" style="color: red;"></div>
{{ f.submit("提交") }}

{{ f.end }}