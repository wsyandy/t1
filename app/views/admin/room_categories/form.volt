{% set f = simple_form([ 'admin', room_category ], ['enctype': 'multipart/form-data', 'class':'ajax_model_form']) %}

{{ f.hidden('parent_id') }}

{{ f.input('name', [ 'label':'名称','width':'50%' ]) }}
{{ f.input('type',['label':'类型','width':'50%']) }}
{{ f.input('rank',['label':'排序', 'width':'50%']) }}
{{ f.select('status',['label':'状态', 'collection': RoomCategories.STATUS, 'width':'50%']) }}
{{ f.file('image',['label':'图片']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}
