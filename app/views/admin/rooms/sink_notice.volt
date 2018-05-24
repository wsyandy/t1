{% set f = simple_form('/admin/rooms/sink_notice?user_id='~user_id, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'room']) %}

{{ f.input('sender_id',['label':'用户ID']) }}

{{ f.input('url',['label':'跳转链接']) }}
{{ f.input('content',['label':'消息内容']) }}
{{ f.input('title',['label':'标题']) }}


<div class="error_reason" style="color: red;"></div>
{{ f.submit('提交') }}

{{ f.end }}

