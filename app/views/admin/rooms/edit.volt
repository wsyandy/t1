{% set f = simple_form(['admin', room], ['class': 'ajax_model_form', 'model': 'room']) %}

{{ f.select('hot',['label':'是否热门','collection':Rooms.HOT]) }}
{{ f.select('status',['label':'状态','collection':Rooms.STATUS]) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}