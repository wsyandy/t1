{% set f = simple_form(['admin', withdraw_historie],['method':'post', 'class':'ajax_model_form', 'model': 'withdraw_historie',
'enctype': 'multipart/form-data']) %}

{{ f.select('status', ['label': '状态', 'collection': WithdrawHistories.STATUS]) }}
{{ f.input('error_reason', ['label': '原因(失败时填写)']) }}
{{ f.submit('保存') }}
<div class="error_reason" style="color:red;"></div>

