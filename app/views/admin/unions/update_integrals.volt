{% set f = simple_form('/admin/unions/update_integrals?id='~id, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'union']) %}

{{ f.input('integrals',[ 'label':'加减分数(扣除未负数)','width':'100%' ]) }}

{{ f.submit('保存') }}

<div class="error_reason" style="color: red"></div>

{{ f.end }}