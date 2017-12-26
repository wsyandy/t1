<a href="/admin/client_themes/new?product_channel_id={{ product_channel_id }}" class="modal_action">新建</a>

{% macro soft_version_info(client_theme) %}
    {{ client_theme.ios_version_code }} |   {{ client_theme.android_version_code }}
{% endmacro %}


{{ simple_table(client_themes,['id':'id','产品渠道':'product_channel_name','客户端主题版本名称': 'version_name', '客户端主题版本code':'version_code',
'状态':'status_text','苹果-适用软件版本号':'ios_version_code','安卓-适用软件版本号':'android_version_code','时间':'created_at_text','编辑': 'edit_link']) }}

<script type="text/template" id="client_theme_tpl">
    <tr id="client_theme_${client_theme.id}">
        <td>${client_theme.id}</td>
        <td>${client_theme.product_channel_name}</td>
        <td>${client_theme.version_name}</td>
        <td>${client_theme.version_code}</td>
        <td>${client_theme.status_text}</td>
        <td>${client_theme.ios_version_code}</td>
        <td>${client_theme.android_version_code}</td>
        <td>${client_theme.created_at_text}</td>
        <td>
            <a href="/admin/client_themes/edit/${client_theme.id}" class="modal_action">编辑</a><br/>
        </td>
    </tr>
</script>

<script type="text/javascript">
    $(function () {
        {% for client_theme in client_themes %}
        var tds = $('#client_theme_{{ client_theme.id }}').find('td');
        $.each(tds, function (index, item) {
            if ($(this).text() == -1) {
                $(this).css('backgroundColor', 'grey');
            }
        });
        {% endfor %}
    });
</script>
