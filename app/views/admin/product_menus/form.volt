{% set f = simple_form([ 'admin', product_menu ], ['enctype': 'multipart/form-data', 'class':'ajax_model_form']) %}

{{ f.input('name', [ 'label':'名称','width':'50%' ]) }}
{{ f.select('type',['label':'类型','collection': room_categories,'text_field':'type','value_field':'type','width':'50%']) }}
{{ f.input('rank',['label':'排序', 'width':'50%']) }}
{{ f.select('status',['label':'状态', 'collection': ProductMenus.STATUS, 'width':'50%']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}