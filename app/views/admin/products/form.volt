{% set f = simple_form(['admin', product], ['class': 'ajax_model_form', 'model': 'product']) %}
  {{ f.hidden('product_group_id', ['value': product.product_group_id]) }}
  {{ f.input('name', ['label': '产品名称']) }}
  {{ f.input('full_name', ['label': '全称']) }}
  {{ f.input('amount', ['label': '金额']) }}
  {{ f.input('diamond', ['label': '钻石']) }}
  {{ f.input('gold',['label':'金币']) }}
  {{ f.input('hi_coins',['label':'Hi币']) }}
  {{ f.input('apple_product_no', ['label': '苹果支付代码']) }}
  {{ f.input('rank', ['label': '排序']) }}
  {{ f.file('icon', ['label': 'icon']) }}
  {{ f.select('status', ['label': '状态', 'collection': Products.STATUS]) }}
  {{ f.submit('保存', ['class': 'btn btn-default btn-primary']) }}
{{ f.end }}