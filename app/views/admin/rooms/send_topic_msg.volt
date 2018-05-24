{% set f = simple_form('/admin/rooms/send_topic_msg?user_id='~user_id, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'room']) %}

{{ f.input('sender_id',['label':'用户ID']) }}


{{ f.input('content',['label':'消息内容']) }}

{{ f.select('content_type',['label':'消息内容类型','collection': content_types,'width':'100%']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('提交') }}

{{ f.end }}

