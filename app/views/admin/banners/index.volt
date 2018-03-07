<a href="/admin/banners/new" class="modal_action">新建</a>

{%- macro product_channel_banners_link(banner) %}
    <a class="modal_action"
       href="/admin/banners/product_channels/{{ banner.id }}">查看({{ banner.product_channel_num }})</a>
{%- endmacro %}

{% macro avatar_image(banner) %}
    <img src="{{ banner.image_small_url }}" height="50"/>
{% endmacro %}

{%- macro platforms_link(banner) %}
    <a class="modal_action" href="/admin/banners/platforms/{{ banner.id }}">查看({{ banner.platform_num }})</a>
{%- endmacro %}



{{ simple_table(banners,['id':'id','排序':'rank', '名称': 'name',
'图片':'avatar_image', '状态':'status_text','时间':'created_at_text','支持的渠道':'product_channel_banners_link','支持的平台':'platforms_link','编辑': 'edit_link']) }}

<script type="text/template" id="banner_tpl">
    <tr id="banner_${banner.id}">
        <td>${banner.id}</td>
        <td>${banner.rank}</td>
        <td>${banner.name}</td>
        <td><img src="${ banner.image_small_url }" height="50"/></td>
        <td>${banner.status_text}</td>
        <td>${banner.created_at_text}</td>
        <td>
            <a class="modal_action" href="/admin/banners/product_channels/${ banner.id }">查看(${banner.product_channel_num})</a>
        </td>
        <td>
            <a class="modal_action" href="/admin/banners/platforms/${ banner.id }">查看(${banner.platform_num})</a>
        </td>
        <td>
            <a href="/admin/banners/edit/${banner.id}" class="modal_action">编辑</a><br/>
        </td>
    </tr>
</script>


<script type="text/javascript">
    $(function () {
        $("#product_channel_id_eq").change(function () {
            $("#search_form").submit();
        });
    });
</script>