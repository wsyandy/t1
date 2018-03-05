{% set f = simple_form(['admin', access_token], ['class': 'ajax_model_form']) %}
{{ f.input('user_id',['label': '用户ID']) }}
{{ f.select('status', ['label': '状态', 'collection':AccessTokens.STATUS]) }}
<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}