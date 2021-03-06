<form action="/admin/weixin_kefu_messages" method="get" name="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="weixin_kefu_message[product_channel_id_eq]" id="product_channel_id_eq">
        {{ options(product_channels, product_channel_id, 'id', 'name') }}
    </select>
    <label for="status_eq">状态</label>
    <select name="weixin_kefu_message[status_eq]" id="status_eq">
        {{ options(WeixinKefuMessages.STATUS, status) }}
    </select>
    <button type="submit" class="ui button">搜索</button>
</form>

<a href="/admin/weixin_kefu_messages/new" class="modal_action">新增</a>

{%- macro message_contnet_link(weixin_kefu_message) %}
    <a href="/admin/weixin_kefu_messages/message_content?id={{ weixin_kefu_message.id }}">添加消息</a>
{%- endmacro %}

{%- macro message_contnet_list(weixin_kefu_message) %}
    <a href="/admin/weixin_kefu_messages/message_content_list?id={{ weixin_kefu_message.id }}">查看消息列表({{ weixin_kefu_message.push_message_num }}
        )</a>
{%- endmacro %}

{%- macro message_contnet_send(weixin_kefu_message) %}
    <a href="/admin/weixin_kefu_messages/message_content_send?id={{ weixin_kefu_message.id }}" id="send_msg">发送消息</a>
{%- endmacro %}

{%- macro support_province_link(weixin_kefu_message) %}
    <a href="/admin/weixin_kefu_messages/support_province/{{ weixin_kefu_message.id }}"
       class="modal_action">支持的省份</a>
{%- endmacro %}

{{ simple_table(weixin_kefu_messages, ['ID':'id', '产品渠道':'product_channel_name', '名称':'name', '状态':'status_text','发送状态':'send_status_text','发送时间':'send_at_text',
'发送统计结果':'remark','消息列表':'message_contnet_list',
'添加消息':'message_contnet_link', '支持的省份':'support_province_link', '发送消息':'message_contnet_send', '操作者':'operator_username','编辑':'edit_link', '终止发送':'delete_link']) }}

<script type="text/template" id="weixin_kefu_message_tpl">
    <tr id="weixin_kefu_message_${weixin_kefu_message.id}">
        <td>${weixin_kefu_message.id}</td>
        <td>${weixin_kefu_message.product_channel_name}</td>
        <td>${weixin_kefu_message.name}</td>
        <td>${weixin_kefu_message.status_text}</td>
        <td>${weixin_kefu_message.send_status_text}</td>
        <td>${weixin_kefu_message.send_at_text}</td>
        <td>${weixin_kefu_message.remark}</td>
        <td>
            <a href="/admin/weixin_kefu_messages/message_content_list?id=${weixin_kefu_message.id}">查看消息列表(${weixin_kefu_message.push_message_num})</a>
        </td>
        <td>
            <a href="/admin/weixin_kefu_messages/message_content?id=${weixin_kefu_message.id}">添加消息</a>
        </td>
        <td>
            <a href="/admin/weixin_kefu_messages/support_province/${ weixin_kefu_message.id }" class="modal_action">支持的省份</a>
        </td>
        <td>
            <a href="/admin/weixin_kefu_messages/message_content_send?id=${weixin_kefu_message.id}"
               id="send_msg">发送消息</a>
        </td>
        <td>${weixin_kefu_message.operator_username}</td>
        <td>
            <a href="/admin/weixin_kefu_messages/edit/${weixin_kefu_message.id}" class="modal_action">编辑</a>
        </td>
        <td>
            <a href="/admin/weixin_kefu_messages/delete/${weixin_kefu_message.id}" class="delete_action"
               data-target="#weixin_kefu_message_${weixin_kefu_message.id}">终止发送</a>
        </td>
    </tr>
</script>

<script type="text/javascript">
    $('body').on('click', '#send_msg', function (e) {
        e.preventDefault();
        if (confirm('确认发送？')) {
            var href = $(this).attr('href');
            $.post(href, '', function (resp) {
                if (0 == resp.error_code) {
                    alert('消息已发送~~');
                } else {
                    alert(resp.error_reason);
                }
            });
        }
    })
</script>