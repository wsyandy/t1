{% set f = simple_form('/admin/hi_coin_histories/create_hi_coins?user_id='~user_id, hi_coin_history, ['method':'POST', 'class': 'ajax_model_form',
    'model': 'hi_coin_history', 'enctype': 'multipart/form-data']) %}

{{ f.input('hi_coins',['label':'房间流水奖励']) }}
{{ f.input('remark',['label':'标记']) }}
{{ f.input('content',['label':'系统消息']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('提交') }}

{{ f.end }}