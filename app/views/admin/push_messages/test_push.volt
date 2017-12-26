{% set f = simple_form(c('/admin/push_messages/test_push/',push_message.id),
    push_message,[ 'method': 'post' , 'class': 'ajax_model_form']) %}

{{ f.input('device_id',['label':'客户端设备id']) }}
{{ f.input('user_id',['label':'微信用户id']) }}
{{ f.submit('提交') }}
<div class="error_reason" style="color: red"></div>
{{ f.end }}