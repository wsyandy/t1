{% set f = simple_form('/admin/award_histories/send_system_message?id='~id~'&user_id='~user_id, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'award_history']) %}

{{ f.select('auth_status',['label':'审核状态','collection': AwardHistories.AUTH_STATUS,'width':'100%']) }}
{{ f.submit('提交') }}

{{ f.end }}


