<a href="/admin/banned_words/new" class="modal_action">新建</a>

{% macro edit_link(banned_word) %}
    <a href="/admin/banned_words/edit/{{ banned_word.id }}" class="modal_action">编辑</a>
{% endmacro %}

{{ simple_table(banned_words,['ID': 'id','违禁词': 'word','创建时间':'created_at_text','编辑':'edit_link']) }}

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

