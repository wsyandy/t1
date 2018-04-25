{% set f = simple_form(['admin', partner_account],['method':'post', 'class':'ajax_model_form']) %}
{{ f.password('password',['label': '密码']) }}
{{ f.select('status', ['label': '状态', 'collection': PartnerAccounts.STATUS]) }}

{{ f.submit('保存') }}
{{ f.end }}