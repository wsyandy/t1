{% set f = simple_form(['admin',weixin_sub_menu], ['class': 'ajax_model_form']) %}
{{ f.input('name', ['label':'子菜单名称(不超过5个字)']) }}
{{ f.input('url', ['label':'子菜单url']) }}
{{ f.input('rank', ['label':'排序(二级菜单从上到下，排序值越小越靠上)']) }}
{{ f.select('type', ['label': '子菜单类型','collection': WeixinMenus.TYPE]) }}
{{ f.hidden('weixin_menu_id') }}
{{ f.submit('保存') }}
{{ f.end }}