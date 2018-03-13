
{%- macro download_link(gift_resource) %}
    <a target="_blank" href="{{ gift_resource.resource_file_url }}">点击下载</a>
{%- endmacro %}

{{ simple_table(gift_resources, [
    '创建时间':'created_at_text','ID': 'id', '状态':'status_text', '备注': 'remark', '下载':'download_link'
]) }}