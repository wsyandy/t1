{% set f = simple_form(['admin', emoticon_image],['method':'post', 'class':'ajax_model_form', 'model': 'emoticon_image',
'enctype': 'multipart/form-data']) %}

{{ f.input('name',['label': '名称']) }}
{{ f.input('code',['label': 'code（不能重复）']) }}
{{ f.input('duration',['label':'持续时间']) }}
{{ f.input('rank', ['label': '排序（不能重复）']) }}
{{ f.select('status', ['label': '状态', 'collection': EmoticonImages.STATUS]) }}
{{ f.file('image', ['label': '图片']) }}
{{ f.file('dynamic_image', ['label': '动态图']) }}
<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}