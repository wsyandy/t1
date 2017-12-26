<a href="/admin/sms_channels/new" class="modal_action">新增</a>

{%- macro product_channels_link(sms_channel) %}
    <a class="modal_action"
       href="/admin/sms_channels/product_channels/{{ sms_channel.id }}">查看产品渠道({{ sms_channel.product_channel_ids_num }}
        )</a>
{%- endmacro %}

{{ simple_table(sms_channels,[
'id': 'id',"名称": 'name','运营商':'mobile_operator_text','消息类型': 'sms_type','排序': 'rank','账号': 'username', '状态': 'status_text','固定短信签名':'signature',
'模版':'template','产品渠道配置':'product_channels_link','编辑': 'edit_link']) }}

<script type="text/template" id="sms_channel_tpl">
    <tr id="sms_channel_${sms_channel.id}">
        <td>${sms_channel.id}</td>
        <td>${sms_channel.name}</td>
        <td>${sms_channel.mobile_operator_text}</td>
        <td>${sms_channel.sms_type}</td>
        <td>${sms_channel.rank}</td>
        <td>${sms_channel.username}</td>
        <td>${sms_channel.status_text}</td>
        <td>${sms_channel.signature}</td>
        <td>${sms_channel.template}</td>
        <td><a class="modal_action" href="/admin/sms_channels/product_channels/${ sms_channel.id }">查看产品渠道(${ sms_channel.product_channel_ids_num } )</a>
        </td>
        <td><a href="/admin/sms_channels/edit/${sms_channel.id}" class="modal_action">编辑</a></td>
    </tr>
</script>


<script type="text/javascript">
    $(function () {
        {% for sms_channel in sms_channels %}
        {% if sms_channel.status != 1 %}
        $("#sms_channel_{{ sms_channel.id }}").css({"background-color": "grey"});
        {% endif %}
        {% endfor %}
    });
</script>