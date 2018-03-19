{% set f = simple_form(['admin',id_card_auth],['class':'ajax_model_form']) %}

{{ f.select('auth_status',['label':'审核状态','collection':IdCardAuths.AUTH_STATUS]) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}