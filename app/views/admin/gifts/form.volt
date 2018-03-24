{% set f = simple_form(['admin', gift],['method':'post', 'class':'ajax_model_form', 'model': 'gift',
    'enctype': 'multipart/form-data']) %}

{{ f.input('name',['label': '名称', 'width':'50%']) }}
{{ f.input('amount',['label': '金额',  'width':'50%']) }}
{{ f.select('status', ['label': '状态', 'collection': Gifts.STATUS,  'width':'33%']) }}
{{ f.select('render_type', ['label': '渲染类型', 'collection': Gifts.RENDER_TYPE,  'width':'33%']) }}
{{ f.select('pay_type', ['label': '支付类型', 'collection': Gifts.PAY_TYPE, 'width':'33%']) }}
{{ f.select('type', ['label': '礼物类型', 'collection': Gifts.TYPE, 'width':'33%']) }}
{{ f.input('rank', ['label': '排序','width':'33%']) }}
{{ f.input('expire_day', ['label': '有效天数', 'width':'33%']) }}
{{ f.input('show_rank', ['label': '礼物展示排序',  'width':'50%']) }}
{{ f.input('expire_time', ['label': '礼物展示过期时间(单位:分钟)',  'width':'50%']) }}
{{ f.file('image', ['label': '图片',  'width':'50%']) }}
{{ f.file('big_image', ['label': '大图',  'width':'50%']) }}
{{ f.file('dynamic_image', ['label': 'gif动态图', 'width':'50%']) }}
{{ f.file('svga_image', ['label': 'svga动态图', 'width':'50%']) }}

{{ f.submit('保存') }}

