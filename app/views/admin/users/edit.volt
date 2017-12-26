{% set f = simple_form([ 'admin', user ], ['class':'ajax_model_form']) %}

{{ f.input('ip',['label': 'IP地址']) }}
{{ f.select('user_type', ['label': '用户类型', 'collection': Users.USER_TYPE,'width':'100%']) }}

{{ f.submit('保存') }}

{{ f.end }}