{% if gift.id %}
    {% set f = simple_form(c('/admin/i_gifts/update?id=', gift.id), gift, ['enctype': 'multipart/form-data', 'class':'ajax_model_form','model': 'gift']) %}
{% else %}
    {% set f = simple_form('/admin/i_gifts/create', gift, ['enctype': 'multipart/form-data', 'class':'ajax_model_form','model': 'gift']) %}
{% endif %}

{{ f.input('name',['label': '名称', 'width':'40%']) }}
{{ f.select('status', ['label': '状态', 'collection': Gifts.STATUS,  'width':'30%']) }}
{{ f.select('render_type', ['label': '渲染类型', 'collection': Gifts.RENDER_TYPE,  'width':'30%']) }}

{{ f.input('amount',['label': '金额',  'width':'33%']) }}
{{ f.select('type', ['label': '礼物类型', 'collection': Gifts.TYPE, 'width':'33%']) }}
{{ f.select('pay_type', ['label': '支付类型', 'collection': Gifts.PAY_TYPE, 'width':'33%']) }}

{{ f.input('expire_day', ['label': '有效天数', 'width':'50%']) }}
{{ f.input('rank', ['label': '排序','width':'50%']) }}

{{ f.input('show_rank', ['label': '礼物特效排序',  'width':'50%']) }}
{{ f.input('expire_time', ['label': '礼物特效过期时间(单位:秒)',  'width':'50%']) }}
{{ f.file('image', ['label': '图片',  'width':'50%']) }}
{{ f.file('big_image', ['label': '大图',  'width':'50%']) }}
{{ f.file('dynamic_image', ['label': 'gif动态图', 'width':'50%']) }}
{{ f.file('svga_image', ['label': 'svga动态图', 'width':'50%']) }}
{{ f.textarea('text_content',['label': '文本内容(昵称:%user_name%;礼物:%gift_name%)'|e ]) }}

<div class="error_reason" style="color: red;"></div>

{{ f.submit('保存') }}

