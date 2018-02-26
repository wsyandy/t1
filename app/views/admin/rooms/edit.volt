{% set f = simple_form(['admin', room], ['class': 'ajax_model_form', 'model': 'room']) %}

{{ f.select('hot',['label':'热门','collection':Rooms.HOT]) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}