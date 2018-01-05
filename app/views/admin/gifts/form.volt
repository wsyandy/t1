{% set f = simple_form(['admin', gift],['method':'post', 'class':'ajax_model_form', 'model': 'gift',
'enctype': 'multipart/form-data']) %}

{{ f.input('name',['label': '名称']) }}
{{ f.input('amount',['label': '金额']) }}
{{ f.select('status', ['label': '状态', 'collection': Gifts.STATUS]) }}
{{ f.input('rank', ['label': '排序']) }}
{{ f.file('image', ['label': '图片']) }}
{{ f.file('dynamic_image', ['label': '动态图']) }}

{{ f.submit('保存') }}

