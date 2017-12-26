{% set f = simple_form(['admin',push_message],['class':'ajax_model_form']) %}

{{ f.select('status',['label':'有效', 'collection': PushMessages.STATUS,'width': '49%' ]) }}
{{ f.input('rank',['label':'排序','width': '49%' ]) }}
{{ f.select('offline_time',['label':'时间','collection':PushMessages.OFFLINE_TIME, 'width':'100%']) }}

{{ f.input('title',['label':'标题']) }}
{{ f.textarea('description',['label': '描述' ]) }}
{#{{ f.textarea('text_content',['label': '文本内容(产品金额:%amount%;产品跳转地址:<a href="%product_url%">点我申请</a>;产品渠道名称:%product_channel_name%)'|e ]) }}#}
{{ f.file('image',['label':'图片']) }}
{{ f.input('url',['label':'跳转地址(下面产品为空)']) }}
{{ f.select('product_id', ['label': '产品(上面url为空)', 'collection': products]) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}

{{ f.end }}