{% set f = simple_form(['admin', product_group], ['class': 'ajax_model_form', 'model': 'product_group']) %}
  {{ f.hidden('product_channel_id', ['value': product_channel_id]) }}
  {{ f.input('name', ['label': '名称']) }}
  {{ f.select('fee_type', ['label': '支付类型', 'collection': ProductGroups.FEE_TYPE]) }}
  {{ f.file('icon', ['label': 'ICON']) }}
  {{ f.input('remark', ['label': '备注']) }}
  {{ f.select('status', ['label': '有效', 'collection': ProductGroups.STATUS]) }}
  {{ f.submit('保存', ['class': 'btn btn-default btn-primary']) }}
{{ f.end }}