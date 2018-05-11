{% set f = simple_form('/admin/rooms/send_msg?user_id='~user_id, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'room']) %}

{{ f.select('action',['label':'action','collection': actions,'width':'100%']) }}
{#{{ f.select('sender_id', ['label': '发送者ID', 'collection': senders,'text_field':'nickname','value_field':'id','width': '100%']) }}#}
{#{{ f.select('gift_id', ['label': '礼物', 'collection': gifts,'text_field':'name','value_field':'id','width': '100%']) }}#}
{{ f.input('sender_id',['label':'用户ID']) }}
{{ f.input('gift_id',['label':'礼物ID']) }}
{{ f.input('num',['label':'红包个数']) }}
{{ f.input('url',['label':'红包跳转链接']) }}

{{ f.input('left_pk_user_id',['label':'左边pk用户ID']) }}
{{ f.input('left_pk_user_score',['label':'左边pk用户分数']) }}
{{ f.input('right_pk_user_id',['label':'右边pk用户ID']) }}
{{ f.input('right_pk_user_score',['label':'右边pk用户分数']) }}

{{ f.input('content',['label':'消息内容']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('提交') }}

{{ f.end }}

