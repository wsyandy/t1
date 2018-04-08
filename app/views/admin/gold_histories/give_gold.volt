{% set f = simple_form('/admin/gold_histories/give_gold?user_id='~user_id, ['method':'POST', 'class': 'ajax_model_form',
    'data-model':'account_history']) %}

{{ f.input('gold',['label':'赠送数量']) }}

<div class="error_reason" style="color: red;"></div>

{{ f.submit('提交') }}

{{ f.end }}