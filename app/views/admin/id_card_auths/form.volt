{% set f = simple_form(['admin', id_card_auths], ['class': 'ajax_model_form', 'model': 'id_card_auths']) %}

{{ f.select('auth_status',['label':'审核状态','collection':IdCardAuths.AUTH_STATUS]) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}