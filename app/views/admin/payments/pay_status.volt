{% set f = simple_form(['admin', payment], ['class': 'ajax_model_form form-horizontal', 'model': 'payment']) %}
  {{ f.select('pay_status', ['label': '支付状态', 'collection': Payments.pay_status]) }}
  {{ f.submit('保存', ['class': 'btn btn-default btn-primary']) }}
{{ f.end }}
