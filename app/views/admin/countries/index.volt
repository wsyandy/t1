{% macro country_image(country) %}
    <img src="{{ country.image_small_url }}" height="50"/>
{% endmacro %}

<form name="search_form" action="/admin/countries" method="get" autocomplete="off" id="search_form">
    <label for="id">ID</label>
    <input name="id" type="text" id="id" value="{{ id }}"/>

    <label for="chinese_name">国家名称</label>
    <input name="chinese_name" type="text" id="chinese_name" value="{{ chinese_name }}"/>

    <button type="submit" class="ui button">搜索</button>
</form>


{{ simple_table(countries, [
    'ID': 'id', 'CODE':'code', '国旗':'country_image', '英文名称': 'english_name',
    '中文名称': 'chinese_name','排序':'rank','状态': 'status_text', '编辑':'edit_link'
]
) }}


<script type="text/template" id="country_tpl">
    <tr id="country_${country.id}">
        <td>${country.id}</td>
        <td>${country.code}</td>
        <td><img src="${country.image_small_url}" height="50" width="50"/></td>
        <td>${country.english_name}</td>
        <td>${country.chinese_name}</td>
        <td>${country.rank}</td>
        <td>${country.status_text}</td>
        <td>
            <a href="/admin/countries/edit/${country.id}" class="modal_action">编辑</a><br/>
        </td>
    </tr>
</script>