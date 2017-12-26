{% set f = simple_form(['admin', operator],['method':'post', 'class':'ajax_model_form']) %}

{{ f.input('username',['label': '用户名']) }}
{{ f.password('password',['label': '密码']) }}
{{ f.select('status', ['label': '状态', 'collection': Operators.STATUS]) }}
{{ f.select('role', ['label': '角色', 'collection': Operators.ROLE]) }}

{{ f.submit('保存') }}


<tr id="message_td" style="display: none;">
    <td colspan="4">
        <span class="error_reason" style="color: red;"></span>
    </td>
</tr>

{{ f.end }}