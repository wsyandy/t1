{% set f = simple_form('/admin/rooms/red_packet?user_id='~user_id, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'room']) %}

{{ f.input('sender_id',['label':'用户ID']) }}

{{ f.input('num',['label':'红包个数']) }}
{{ f.input('url',['label':'跳转链接']) }}


<div class="error_reason" style="color: red;"></div>
{{ f.submit('提交') }}

{{ f.end }}


