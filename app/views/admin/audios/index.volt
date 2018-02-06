<a href="/admin/audios/new" class="modal_action">新增</a>


{%- macro edit_link(audio) %}
    <a href="/admin/audios/edit/{{ audio.id }}" class="modal_action">编辑</a>
{%- endmacro %}

{%- macro chapter_link(audio) %}
    <a href="/admin/audio_chapters?audio_id={{ audio.id }}" >章节</a>
{%- endmacro %}

共{{ audios.total_entries }}个

{{ simple_table(audios, [
"ID": 'id', "名称": 'name', '类型':'audio_type_text',"有效": 'status_text', "排名": 'rank','章节':'chapter_link', '编辑': 'edit_link'
]) }}

<script type="text/template" id="audio_tpl">
    <tr id="audio_${ audio.id }">
        <td>${audio.id}</td>
        <td>${audio.name}</td>
        <td>${audio.audio_type_text}</td>
        <td>${audio.status_text}</td>
        <td>${audio.rank}</td>
        <td><a href="/admin/audio_chapters?audio_id=${ audio.id }" >章节</a></td>
        <td><a href="/admin/audios/edit/${audio.id}" class="modal_action">编辑</a></td>
    </tr>
</script>