{% set f = simple_form('/admin/broadcasts/compile_user?user_id='~user_id,user,['method':'POST','class':'ajax_model_form','data_modal':'user']) %}
{{ f.input('nickname',['label':'姓名']) }}
{{ f.select('sex',['label':'性别','collection':Users.SEX]) }}
{{ f.file('avatar',['label':'头像']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('提交') }}

{{ f.end }}