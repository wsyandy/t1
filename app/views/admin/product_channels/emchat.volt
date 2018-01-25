{% set f = simple_form(['admin',product_channel],['method':'post', 'class':'ajax_model_form']) %}


{{ f.input('emchat_client_id',[ 'label': 'emchat_client_id' ]) }}
{{ f.input('emchat_client_secret',[ 'label': 'emchat_client_secret' ]) }}
{{ f.input('emchat_app_name',['label':'emchat_app_name']) }}
{{ f.input('emchat_org_name',['label':'emchat_org_name']) }}
{{ f.input('emchat_host',['label':'emchat_host']) }}

{{ f.submit('保存') }}
{{ f.end }}