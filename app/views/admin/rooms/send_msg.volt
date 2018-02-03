{% set f = simple_form('/admin/rooms/send_msg?user_id='~user_id, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'room']) %}

{{ f.select('action',['label':'action','collection': ACTIONS,'width':'100%']) }}
{{ f.select('sender_id', ['label': '发送者', 'collection': senders,'text_field':'nickname','value_field':'id','width': '100%']) }}
{{ f.select('receiver_id', ['label': '接收者', 'collection': receivers,'text_field':'nickname','value_field':'id','width': '100%']) }}
{{ f.select('gift_id', ['label': '礼物', 'collection': gifts,'text_field':'name','value_field':'id','width': '100%']) }}
{{ f.input('content',['label':'消息内容']) }}



<div class="error_reason" style="color: red;"></div>
{{ f.submit('提交') }}

{{ f.end }}

