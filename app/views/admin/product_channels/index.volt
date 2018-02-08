<form action="/admin/product_channels" method="get" class="search_form" autocomplete="off">

    {% if isAllowed('product_channels','new') %}
        <a href="/admin/product_channels/new" class="modal_action">新建</a>
    {% endif %}

    <label for="id_eq">ID</label>
    <input name="product_channel[id_eq]" type="text" id="id_eq"/>

    <label for="name_eq">产品渠道名称</label>
    <select name="product_channel[name_eq]" id="name_eq" class="selectpicker" data-live-search="true">
        {{ options(all_product_channels, '', 'name', 'name') }}
    </select>

    <button type="submit" class="ui button">搜索</button>
</form>

{%- macro avatar_link(product_channel) %}
    <img src="{{ product_channel.avatar_small_url }}" height="50" width="50"/>
{%- endmacro %}

{%- macro weixin_link(product_channel) %}
    {% if isAllowed('product_channels','weixin_config') %}
        <a href="/admin/product_channels/weixin_config/{{ product_channel.id }}" class="modal_action">微信配置</a><br/>
    {% endif %}
    {% if isAllowed('product_channels','weixin_menu') %}
        <a href="/admin/product_channels/weixin_menu/{{ product_channel.id }}" class="modal_action">微信菜单</a><br/>
    {% endif %}
    {% if isAllowed('product_channels','generate_weixin_qrcode') %}
        <a href="/admin/product_channels/generate_weixin_qrcode/{{ product_channel.id }}"
           class="modal_action">生成微信二维码</a>
    {% endif %}
    <br/>
    {% if isAllowed('product_channels','touch_config') %}
        <a href="/admin/product_channels/touch_config/{{ product_channel.id }}" class="modal_action">H5配置</a><br/>
    {% endif %}
    {% if isAllowed('product_channels','web_config') %}
        <a href="/admin/product_channels/web_config/{{ product_channel.id }}" class="modal_action">web配置</a><br/>
    {% endif %}
{%- endmacro %}

{% macro oper_link(product_channel) %}
    {% if isAllowed('product_channels','edit') %}
        <a href="/admin/product_channels/edit/{{ product_channel.id }}" class="modal_action">编辑</a><br/>
    {% endif %}
    {% if isAllowed('product_channels','system_tips') %}
        <a href="/admin/product_channels/system_tips/{{ product_channel.id }}" class="modal_action">公告配置</a><br/>
    {% endif %}
    {#<a href="/admin/client_themes?client_theme[product_channel_id_eq]={{ product_channel.id }}">客户端主题</a><br/>#}
    {% if isAllowed('product_channels','push') %}
        <a href="/admin/product_channels/push/{{ product_channel.id }}" class="modal_action">个推配置</a><br/>
    {% endif %}
    {#<a href="/admin/product_channels/agora/{{ product_channel.id }}" class="modal_action">声网配置</a><br/>#}
    {#<a href="/admin/product_channels/emchat/{{ product_channel.id }}" class="modal_action">环信配置</a><br/>#}
    {#<a href="/admin/product_channels/copy?id={{ product_channel.id }}" class="modal_action">复制产品渠道到</a><br/>#}
    {% if isAllowed('product_channels','getui_global_push') %}
        <a href="/admin/product_channels/getui_global_push?id={{ product_channel.id }}" class="modal_action">个推全局推送</a>
    {% endif %}
{% endmacro %}

{%- macro company_info(product_channel) %}
    公司名称: {{ product_channel.company_name }} <br/>
    客服电话: {{ product_channel.service_phone }}<br/>
    ICP备案: {{ product_channel.icp }}
{%- endmacro %}

{%- macro client_info(product_channel) %}
    ios客户端稳定版本号: {{ product_channel.apple_stable_version }} <br/>
    ios主题稳定版本号: {{ product_channel.ios_client_theme_stable_version }} <br/>
    安卓客户端稳定版本号: {{ product_channel.android_stable_version }} <br/>
    安卓主题稳定版本号: {{ product_channel.android_client_theme_stable_version }}
{%- endmacro %}

{%- macro test_client_info(product_channel) %}
    ios测试主题版本号: {{ product_channel.ios_client_theme_test_version }} <br/>
    ios海外主题版本号: {{ product_channel.ios_client_theme_foreign_version_code }}
{%- endmacro %}

{%- macro product_group_link(object) %}
    {% if isAllowed('product_groups','index') %}
        <a href="/admin/product_groups?product_channel_id={{ object.id }}">计费配置</a>
    {% endif %}
{%- endmacro %}

{{ simple_table(product_channels, ['ID': 'id', '产品渠道名称': 'name', 'Code':'code', '计费配置': 'product_group_link', 'Icon':'avatar_link',
    '公司信息':'company_info', '版本信息': 'client_info', 'ios测试主题':'test_client_info',
    '状态': 'status_text', '微信':'weixin_link',
    '操作':'oper_link' ]) }}

<script type="text/template" id="product_channel_tpl">
    <tr id="product_channel_${product_channel.id}">
        <td>${product_channel.id}</td>
        <td>${product_channel.name}</td>
        <td>${product_channel.code}</td>
        {% if isAllowed('product_groups','index') %}
            <td><a href="/admin/product_groups?product_channel_id=${product_channel.id}">计费配置</a></td>
        {% endif %}
        <td><img src="${product_channel.avatar_small_url}" height="50" width="50"/></td>
        <td>
            公司名称: ${product_channel.company_name}<br/>
            客服电话: ${product_channel.service_phone}<br/>
            ICP备案: ${product_channel.icp}
        </td>
        <td>
            ios客户端稳定版本号: ${product_channel.apple_stable_version } <br/>
            ios主题稳定版本号: ${product_channel.ios_client_theme_stable_version } <br/>
            安卓客户端稳定版本号: ${product_channel.android_stable_version } <br/>
            安卓主题稳定版本号: ${product_channel.android_client_theme_stable_version }
        </td>
        <td>
            ios测试主题版本号: ${product_channel.ios_client_theme_test_version } <br/>
            ios海外主题版本号: ${product_channel.ios_client_theme_foreign_version_code }
        </td>
        <td>${product_channel.status_text}</td>
        <td>
            {% if isAllowed('product_channels','weixin_config') %}
                <a href="/admin/product_channels/weixin_config/${product_channel.id}" class="modal_action">微信配置</a><br/>
            {% endif %}
            {% if isAllowed('product_channels','weixin_menu') %}
                <a href="/admin/product_channels/weixin_menu/${product_channel.id}" class="modal_action">微信菜单</a><br/>
            {% endif %}
            {% if isAllowed('product_channels','generate_weixin_qrcode') %}
                <a href="/admin/product_channels/generate_weixin_qrcode/${product_channel.id}"
                   class="modal_action">生成微信二维码</a><br/>
            {% endif %}
            {% if isAllowed('product_channels','touch_config') %}
                <a href="/admin/product_channels/touch_config/${product_channel.id}" class="modal_action">H5配置</a><br/>
            {% endif %}
            {% if isAllowed('product_channels','web_config') %}
                <a href="/admin/product_channels/web_config/${product_channel.id}" class="modal_action">web配置</a><br/>
            {% endif %}
        </td>
        <td>
            {% if isAllowed('product_channels','edit') %}
                <a href="/admin/product_channels/edit/${product_channel.id}" class="modal_action">编辑</a><br/>
            {% endif %}
            {% if isAllowed('product_channels','system_tips') %}
                <a href="/admin/product_channels/system_tips/${ product_channel.id }" class="modal_action">公告配置</a><br/>
            {% endif %}
            {#<a href="/admin/client_themes?client_theme[product_channel_id_eq]=${ product_channel.id }">客户端主题</a><br/>#}
            {% if isAllowed('product_channels','push') %}
                <a href="/admin/product_channels/push/${ product_channel.id }" class="modal_action">个推配置</a><br/>
            {% endif %}
            {#<a href="/admin/product_channels/agora/${ product_channel.id }" class="modal_action">声网配置</a><br/>#}
            {#<a href="/admin/product_channels/emchat/${ product_channel.id }" class="modal_action">环信配置</a><br/>#}
            {#<a href="/admin/product_channels/copy?id=${ product_channel.id }" class="modal_action">复制产品渠道到</a><br/>#}
            {% if isAllowed('product_channels','getui_global_push') %}
                <a href="/admin/product_channels/getui_global_push?id=${ product_channel.id }"
                   class="modal_action">个推全局推送</a>
            {% endif %}
        </td>
    </tr>
</script>
<script type="text/javascript">
    $(function () {
        $('.selectpicker').selectpicker();
    });
</script>