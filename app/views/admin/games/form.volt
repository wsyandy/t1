{% set f = simple_form(['admin',game],['class':'ajax_model_form', 'model': 'game']) %}

{{ f.input('name',['label':'游戏名称','width': '100%']) }}
{{ f.file('icon',['label':'icon']) }}
{{ f.select('status',['label':'状态', 'collection': Games.STATUS,'width': '50%' ]) }}
{{ f.select('clazz',['label':'网关', 'collection': clazz_names,'width': '50%' ]) }}
{{ f.input('url',['label':'跳转地址', 'width': '100%']) }}
{{ f.submit('保存') }}
{{ f.end }}
