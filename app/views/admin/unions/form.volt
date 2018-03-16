{% set f = simple_form([ 'admin', union ], ['class':'ajax_model_form']) %}

{{ f.input('name',[ 'label':'名称','width':'50%' ]) }}
{{ f.select('status',[ 'label':'状态' , 'collection': Unions.STATUS, 'width':'50%']) }}
{% if union.type == 1 %}
    {{ f.select('auth_status',[ 'label':'审核状态' , 'collection': Unions.AUTH_STATUS, 'width':'100%']) }}
{% endif %}

{% if union.type == 2 %}
    {{ f.select('recommend',['label':'推荐','collection':Unions.RECOMMEND,'width':'100%']) }}
{% endif %}

{{ f.submit('保存') }}

{{ f.end }}