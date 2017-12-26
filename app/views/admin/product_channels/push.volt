{% set f = simple_form(['admin',product_channel],['method':'post', 'class':'ajax_model_form']) %}

{{ f.input('android_app_id',[ 'label': '安卓AppId' ]) }}
{{ f.input('android_app_key',[ 'label': '安卓AppKey' ]) }}
{{ f.input('android_app_secret',[ 'label': '安卓AppSecret' ]) }}
{{ f.input('android_app_master_secret',[ 'label': '安卓AppMasterSecret' ]) }}
{{ f.input('ios_app_id',[ 'label': '个推ios AppId' ]) }}
{{ f.input('ios_app_key',[ 'label': '个推ios AppKey' ]) }}
{{ f.input('ios_app_secret',[ 'label': '个推ios AppSecret' ]) }}
{{ f.input('ios_app_master_secret',[ 'label': '个推ios AppMasterSecret' ]) }}

{{ f.submit('保存') }}
{{ f.end }}
