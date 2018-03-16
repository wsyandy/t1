{% set f = simple_form('/admin/unions/settled_amount', ['method':'POST','data-model':'union','class':'ajax_model_form']) %}

<input type="hidden" name="union_id" value="{{ union_id }}">
{{ f.input('amount',[ 'label':'金额', 'value':amount]) }}

{{ f.submit('保存') }}

{{ f.end }}