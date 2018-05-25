{% set f = simple_form('/admin/rooms/send_gift?user_id='~user_id, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'room']) %}

{{ f.input('sender_id',['label':'用户ID']) }}
{{ f.input('gift_id',['label':'礼物ID']) }}


<div class="error_reason" style="color: red;"></div>
{{ f.submit('提交') }}

{{ f.end }}

