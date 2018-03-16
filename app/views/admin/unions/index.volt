{% macro oper_link(union) %}
    {% if isAllowed('unions','edit') and 3 == union.auth_status %}
        <a href="/admin/unions/edit/{{ union.id }}" class="modal_action">编辑</a><br/>
    {% endif %}
    {% if isAllowed('unions','add_user') and 1 == union.auth_status %}
        <a href="/admin/unions/users/{{ union.id }}">添加公会成员</a><br/>
        <a href="/admin/rooms?union_id={{ union.id }}">房间列表</a><br/>
        <a href="/admin/unions/settled_amount?union_id={{ union.id }}" class="modal_action">结算金额</a><br/>
    {% endif %}
{% endmacro %}

{{ simple_table(unions, [
    'ID': 'id',
    '公会名称': 'name',
    '用户': 'user_nickname',
    '真实姓名': 'id_name',
    '身份证':'id_no',
    '支付宝':'alipay_account',
    '状态': 'status_text',
    '审核状态': 'auth_status_text',
    '操作' :'oper_link'
]) }}