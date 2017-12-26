{% set f = simple_form([ 'admin', sms_channel ], ['class':'ajax_model_form']) %}

{{ f.input('name',[ 'label':'名称','width':'30%' ]) }}
{{ f.select('status',[ 'label':'状态' , 'collection': SmsChannels.STATUS, 'width':'15%']) }}
{{ f.input('rank',[ 'label':'排名','width': '15%' ]) }}
{{ f.input('template',[ 'label':'模版ID','width':'40%' ]) }}

{{ f.input('username',[ 'label':'账号','width':'50%' ]) }}
{{ f.input('password',[ 'label':'密码', 'as': 'text','width':'50%' ]) }}

{{ f.select('sms_type',[ 'label':'消息类型', 'collection': SmsChannels.SMS_TYPE , 'width':'25%']) }}
{{ f.select('mobile_operator',[ 'label':'运营商' , 'collection': SmsChannels.MOBILE_OPERATOR, 'width':'25%']) }}
{{ f.select('clazz',['label': '网关类', 'collection': gatewayNames,'width': '50%']) }}

{{ f.input('url',[ 'label':'提交地址' ]) }}
{{ f.input('signature',['label': '固定短信签名']) }}

{{ f.submit('保存') }}

{{ f.end }}