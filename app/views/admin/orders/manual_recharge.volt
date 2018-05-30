{% set f = simple_form('/admin/orders/manual_recharge?user_id='~user_id, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'orders']) %}

{{ f.input('amount',['label':'充值金额']) }}
{{ f.input('paid_amount',['label':'实际到账金额']) }}
{{ f.input('diamond',['label':'增加钻石数量']) }}
{{ f.input('gold',['label':'增加金币数量']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('提交') }}

{{ f.end }}