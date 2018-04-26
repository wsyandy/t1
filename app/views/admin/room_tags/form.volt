{% set f = simple_form([ 'admin', room_tag ], ['enctype': 'multipart/form-data', 'class':'ajax_model_form']) %}

{{ f.input('name', [ 'label':'名称']) }}
{{ f.input('rank',['label':'排序']) }}
{{ f.select('status',['label':'状态', 'collection': RoomTags.STATUS]) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}
