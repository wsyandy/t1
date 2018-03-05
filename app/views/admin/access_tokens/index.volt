{{ simple_table(access_tokens, [
    'ID': 'id', '用户': 'user_nickname', 'status':'status_text','过期时间':'expired_at_text','创建时间': 'created_at_text', '编辑':'edit_link'
]) }}

<script type="text/template" id="access_token_tpl">
    <tr id="access_token_${access_token.id}">
        <td>${access_token.id}</td>
        <td>${access_token.user_nickname}</td>
        <td>${access_token.status_text}</td>
        <td>${access_token.expired_at_text}</td>
        <td>${access_token.created_at_text}</td>

        <td>
            <a href="/admin/access_tokens/edit/${access_token.id}" class="modal_action">编辑</a>
        </td>
    </tr>
</script>