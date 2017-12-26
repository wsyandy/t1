{% set f = simple_form(['admin',weixin_menu_template], ['class': 'ajax_model_form']) %}
{{ f.input('name', ['label':'模板名称']) }}
{{ f.submit('保存') }}
{{ f.end }}