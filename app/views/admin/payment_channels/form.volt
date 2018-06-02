{% set f = simple_form(['admin', payment_channel], ['class': 'ajax_model_form', 'model': 'payment_channel']) %}
  {{ f.input('name', ['label': '通道名称','width':'50%']) }}
  {{ f.input('mer_name', ['label': '商户名称','width':'50%']) }}
  {{ f.input('mer_no', ['label': '商户号','width':'50%']) }}
  {{ f.input('app_id', ['label': 'APP ID','width':'50%']) }}
  {{ f.textarea('app_key',['label':'app密钥(或支付宝公钥或微信商户密钥)']) }}
  {{ f.textarea('app_password',['label':'app私钥(或支付宝商户密钥)']) }}
  {{ f.input('fee', ['label': '费率','width':'33%']) }}
  {{ f.input('rank', ['label': '排序','width':'3%']) }}
  {{ f.select('status', ['label': '有效', 'collection': PaymentChannels.STATUS,'width':'33%']) }}
  {{ f.select('clazz', ['label': '网关', 'collection': clazz_names, 'blank':true,'width':'50%']) }}
  {{ f.select('payment_type', ['label': '支付类型', 'collection': PaymentChannels.PAYMENT_TYPE, 'blank':true,'width':'50%']) }}
  {{ f.input('gateway_url', ['label': '支付地址']) }}
  {{ f.input('android_version_code', ['label': '安卓版本号','width':'50%']) }}
  {{ f.input('ios_version_code', ['label': 'ios版本号','width':'50%']) }}
  {{ f.submit("保存", ['class': 'btn btn-default btn-primary']) }}
{{ f.end }}