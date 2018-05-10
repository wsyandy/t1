{% set f = simple_form('/admin/backpacks/give_backpacks?user_id='~user_id, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'backpacks']) %}

{{ f.input('target_id',['label':'礼物ID']) }}
{{ f.input('number',['label':'数量']) }}

<div class="error_reason" style="color: red;"></div>

{{ f.submit('提交') }}

{{ f.end }}