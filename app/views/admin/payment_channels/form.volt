{% set f = simple_form(['admin', payment_channel], ['class': 'ajax_model_form', 'model': 'payment_channel']) %}
  {{ f.input('name', ['label': '通道名称']) }}
  {{ f.input('mer_name', ['label': '商户名称']) }}
  {{ f.input('mer_no', ['label': '商户号']) }}
  {{ f.input('fee', ['label': '费率']) }}
  {{ f.input('app_id', ['label': 'app_id']) }}
  {{ f.input('app_key', ['label': 'app_key']) }}
  {{ f.input('app_password', ['label': 'app_password']) }}
  {{ f.select('clazz', ['label': '网关', 'collection': PaymentChannels.gateway_classes]) }}
  {{ f.select('payment_type', ['label': '支付类型', 'collection': PaymentChannels.payment_type]) }}
  {{ f.input('gateway_url', ['label': '支付地址']) }}
  {{ f.input('rank', ['label': '排序']) }}
  {{ f.select('status', ['label': '有效', 'collection': PaymentChannels.STATUS]) }}
  {{ f.submit("保存", ['class': 'btn btn-default btn-primary']) }}
{{ f.end }}