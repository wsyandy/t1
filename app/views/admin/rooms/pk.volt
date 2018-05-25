{% set f = simple_form('/admin/rooms/pk?user_id='~user_id, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'room']) %}

{{ f.input('sender_id',['label':'用户ID']) }}

{{ f.input('left_pk_user_id',['label':'左边pk用户ID']) }}
{{ f.input('left_pk_user_score',['label':'左边pk用户分数']) }}
{{ f.input('right_pk_user_id',['label':'右边pk用户ID']) }}
{{ f.input('right_pk_user_score',['label':'右边pk用户分数']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('提交') }}

{{ f.end }}


