{% set f = simple_form('/admin/i_gold_histories/give_i_gold?user_id='~user_id, ['method':'POST', 'class': 'ajax_model_form',
    'data-model':'i_gold_history']) %}

{{ f.input('i_gold',['label':'赠送数量(一次最多赠送100个)']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('提交') }}

{{ f.end }}