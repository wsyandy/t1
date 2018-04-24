{% set f = simple_form(c('/admin/unions/auth?id=', id),['method':'post', 'class':'ajax_model_form','data-model':'union']) %}

{{ f.input('amount',[ 'label':'返还金额','width':'50%' ]) }}
{{ f.select('auth_status',[ 'label':'审核状态' , 'collection': auth_status, 'width':'50%']) }}

{{ f.submit('保存') }}
<div class="error_reason" style="color: red"></div>
{{ f.end }}

