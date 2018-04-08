{% set f = simple_form(['admin', room], ['class': 'ajax_model_form', 'model': 'room']) %}

{{ f.select('hot',['label':'是否热门','collection':Rooms.HOT, 'width':'50%']) }}
{{ f.select('new',['label':'是否最新','collection':Rooms.NEW, 'width':'50%']) }}
{{ f.select('status',['label':'状态','collection':Rooms.STATUS, 'width':'50%']) }}
{{ f.select('theme_type',['label':'电台主题','collection':Rooms.THEME_TYPE, 'width':'50%']) }}
{{ f.select('top',['label':'新人房间','collection':Rooms.TOP, 'width':'50%']) }}
{{ f.select('top',['label':'绿色房间','collection':Rooms.TOP, 'width':'50%']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}