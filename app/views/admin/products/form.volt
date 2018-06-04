{% set f = simple_form(['admin', product], ['class': 'ajax_model_form', 'model': 'product']) %}
  {{ f.hidden('product_group_id', ['value': product.product_group_id]) }}
  {{ f.input('name', ['label': '产品名称','width':'50%']) }}
  {{ f.input('full_name', ['label': '全称','width':'50%']) }}
  {{ f.input('amount', ['label': '金额','width':'50%']) }}
  {{ f.input('hi_coins',['label':'Hi币','width':'50%']) }}
  {{ f.input('diamond', ['label': '钻石','width':'50%']) }}
  {{ f.input('gold',['label':'金币','width':'50%']) }}
  {{ f.input('draw_num',['label':'赠送砸金蛋次数']) }}
  {{ f.input('apple_product_no', ['label': '苹果支付代码']) }}
  {{ f.input('rank', ['label': '排序', 'width':'50%']) }}
 {{ f.select('status', ['label': '状态', 'collection': Products.STATUS, 'width':'50%']) }}
  {{ f.file('icon', ['label': 'icon']) }}
  {{ f.submit('保存', ['class': 'btn btn-default btn-primary']) }}
{{ f.end }}