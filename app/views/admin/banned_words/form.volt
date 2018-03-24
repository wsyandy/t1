{% set f = simple_form([ 'admin', banned_word ], ['enctype': 'multipart/form-data', 'class':'ajax_model_form']) %}

{{ f.input('word',[ 'label':'违禁词']) }}

{{ f.submit('保存') }}
{{ f.end }}
