{% set f = simple_form(['admin', room], ['class': 'ajax_model_form', 'model': 'room']) %}

{{ f.select('hot',['label':'是否热门','collection':Rooms.HOT]) }}
{{ f.select('new',['label':'是否最新','collection':Rooms.NEW]) }}
{{ f.select('status',['label':'状态','collection':Rooms.STATUS]) }}
{{ f.select('theme_type',['label':'电台主题','collection':Rooms.THEME_TYPE]) }}
{{ f.select('top',['label':'是否置顶','collection':Rooms.TOP]) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}