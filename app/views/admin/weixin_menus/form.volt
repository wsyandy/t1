{% set f = simple_form(['admin',weixin_menu], ['class': 'ajax_model_form']) %}
{{ f.input('name', ['label':'菜单名称(不超过5个字)']) }}
{{ f.input('url', ['label':'菜单url']) }}
{{ f.input('rank', ['label':'排序(一级菜单从左到右，排序值越小越靠左)']) }}
{{ f.select('type', ['label': '菜单类型','collection': WeixinMenus.TYPE]) }}
{{ f.hidden('weixin_menu_template_id') }}
{{ f.submit('保存') }}
{{ f.end }}