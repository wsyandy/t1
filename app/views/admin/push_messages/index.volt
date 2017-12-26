<a href="/admin/push_messages/new" class="modal_action">新增</a>

{%- macro image_link(push_message) %}
    <img src="{{ push_message.image_small_url }}" height="50" width="50"/>
{%- endmacro %}

{%- macro condition_strategy_link(push_message) %}
    <a href="/admin/push_messages/platforms?id={{ push_message.id }}" class="modal_action">平台配置</a><br/>
    <a href="/admin/push_messages/product_channel_ids?id={{ push_message.id }}" class="modal_action">产品渠道配置</a>
{%- endmacro %}

{%- macro push_link(push_message) %}
    <a href="/admin/push_messages/test_push?id={{ push_message.id }}&form=1" class="modal_action">测试推送</a>
{%- endmacro %}

{{ simple_table(push_messages, ['ID':'id','排序(倒序)':'rank', '时间':'offline_time_text','标题': 'title','图片': 'image_link',
'跳转地址': 'url','资源名称':'product_name','状态':'status_text','配置条件':'condition_strategy_link',
'测试推送':'push_link','编辑':'edit_link'
]) }}

<script type="text/template" id="push_message_tpl">
    <tr id="push_message_${push_message.id}">
        <td>${push_message.id}</td>
        <td>${push_message.rank}</td>
        <td>${push_message.offline_time_text}</td>
        <td>${push_message.title}</td>
        <td><img src="${push_message.image_small_url}" height="50" width="50"/></td>
        <td>${push_message.url}</td>
        <td>${push_message.product_name}</td>
        <td>${push_message.status_text}</td>
        <td>
            <a href="/admin/push_messages/platforms?id=${push_message.id}" class="modal_action">平台配置</a><br/>
            <a href="/admin/push_messages/product_channel_ids?id=${push_message.id}" class="modal_action">产品渠道配置</a>
        </td>
        <td><a href="/admin/push_messages/test_push?id=${push_message.id}&form=1" class="modal_action">测试推送</a></td>
        <td><a href="/admin/push_messages/edit/${push_message.id}" class="modal_action">编辑</a></td>
    </tr>
</script>


<script type="text/javascript">
    $(function () {
        {% for push_message in push_messages %}
        {% if push_message.status != 1 %}
        $("#push_message_{{ push_message.id }}").css({"background-color": "grey"});
        {% endif %}
        {% endfor %}
    });
</script>