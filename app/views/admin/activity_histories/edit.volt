{% set f = simple_form(['admin', activity_history], ['class': 'ajax_model_form', 'model': 'activity_history']) %}

{{ f.input('good_number',['label': '靓号id']) }}
{{ f.select('auth_status', ['label': '审核状态', 'collection':ActivityHistories.AUTH_STATUS]) }}
<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}