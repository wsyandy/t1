<form action="/admin/banned_words" method="get" class="search_form" autocomplete="off" id="search_form">
    <a href="/admin/banned_words/new" class="modal_action">新建</a>
    <label for="word_eq">违禁词</label>
    <input name="banned_word[word_eq]" type="text" id="id_eq"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{% macro edit_link(banned_word) %}
    <a href="/admin/banned_words/edit/{{ banned_word.id }}" class="modal_action">编辑</a>
{% endmacro %}

{{ simple_table(banned_words,['ID': 'id','违禁词': 'word','创建时间':'created_at_text','编辑':'edit_link',
    '删除':'delete_link']) }}

<script type="text/template" id="banned_word_tpl">
    <tr id="banned_word_${banned_word.id}">
        <td>${banned_word.id}</td>
        <td>${banned_word.word}</td>
        <td>${banned_word.created_at_text}</td>
        <td>
            <a href="/admin/banned_words/edit/${banned_word.id}" class="modal_action">编辑</a>
        </td>
    </tr>
</script>

