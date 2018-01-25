{% set f = simple_form(['admin',product_channel],['method':'post', 'class':'ajax_model_form']) %}

{{ f.input('agora_app_id',[ 'label': 'agora_app_id' ]) }}
{{ f.input('agora_app_certificate',[ 'label': 'agora_app_certificate' ]) }}

{{ f.submit('保存') }}
{{ f.end }}