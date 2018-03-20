{% set f = simple_form(['admin',banner],['method':'post', 'class':'ajax_model_form']) %}
{{ f.hidden('product_channel_id') }}

{{ f.input('name', ['label': '名称', 'width':'50%']) }}
{{ f.input('rank', ['label': '排序', 'width':'50%']) }}

{{ f.select('new', ['label': '是否最新', 'collection': Banners.NEW, 'width':'50%']) }}
{{ f.select('hot', ['label': '是否热门', 'collection': Banners.HOT, 'width':'50%']) }}

{{ f.select('type', ['label': '适用页面', 'collection': types, 'width':'50%']) }}


{{ f.select('material_type', ['label': '产品类型', 'collection': Banners.MATERIAL_TYPE, 'width':'50%']) }}
{{ f.input('material_ids', ['label': '房间ID', 'width':'50%']) }}

{{ f.select('status', ['label': '状态', 'collection': Banners.STATUS, 'width':'50%']) }}
{{ f.input('url', ['label': 'URL(例: app://products/detail?id=1 或 http://www.baidu.com)']) }}
{{ f.file('image',['label':'上传图片']) }}

<div class="error_reason" style="color: red;"></div>

{{ f.submit('保存') }}
{{ f.end }}