{% set f = simple_form(['admin', gift],['method':'post', 'class':'ajax_model_form', 'model': 'gift',
    'enctype': 'multipart/form-data']) %}

{{ f.input('name',['label': '名称', 'width':'50%']) }}
{{ f.input('amount',['label': '金额',  'width':'50%']) }}
{{ f.select('status', ['label': '状态', 'collection': Gifts.STATUS,  'width':'50%']) }}
{{ f.select('render_type', ['label': '渲染类型', 'collection': Gifts.RENDER_TYPE,  'width':'50%']) }}
{{ f.input('rank', ['label': '排序']) }}
{{ f.file('image', ['label': '图片',  'width':'50%']) }}
{{ f.file('big_image', ['label': '大图',  'width':'50%']) }}
{{ f.file('dynamic_image', ['label': 'gif动态图', 'width':'50%']) }}
{{ f.file('svga_image', ['label': 'svga动态图', 'width':'50%']) }}

{{ f.submit('保存') }}

