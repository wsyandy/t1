{% set f = simple_form([ 'admin', room_category_keyword ], ['enctype': 'multipart/form-data', 'class':'ajax_model_form']) %}

{{ f.hidden('room_category_id') }}
{{ f.input('name', [ 'label':'名称']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}
