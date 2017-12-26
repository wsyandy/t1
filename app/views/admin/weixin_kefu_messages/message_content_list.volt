{%- macro del_message_content(push_message) %}
    <a href="/admin/weixin_kefu_messages/delete_message_content?id={{ push_message.id }}" id="add_message_content">删除</a>
{%- endmacro %}

{%- macro image_link(push_message) %}
    <img src="{{ push_message.image_small_url }}" height="50" width="50"/>
{%- endmacro %}

<input type="hidden" id="weixin_kefu_message_id" value="{{ weixin_kefu_message_id }}">

<a href="/admin/weixin_kefu_messages">返回</a>

{{ simple_table(push_messages, ['ID':'id', '排序':'rank', '标题': 'title','描述':'description','图片': 'image_link','跳转地址': 'url',
'资源名称':'material_name','状态':'status_text','跟踪标识':'tracker_no','条件':'condition_strategy_text','删除':'del_message_content'
]) }}

<script type="text/javascript">
    $('body').on('click', '#add_message_content', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        var weixin_kefu_message_id = $("#weixin_kefu_message_id").val();
        href += '&weixin_kefu_message_id=' + weixin_kefu_message_id;
        $.post(href, '', function (resp) {
            if (0 !== resp.error_code) {
                alert(resp.error_reason);
                return;
            }
            location.reload();
        })
    })
</script>