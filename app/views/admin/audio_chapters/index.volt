<a href="/admin/audio_chapters/new?audio_id={{ audio_id }}" class="modal_action">新增</a>


{%- macro edit_link(audio_chapter) %}
    <a href="/admin/audio_chapters/edit/{{ audio_chapter.id }}" class="modal_action">编辑</a>
{%- endmacro %}

{% macro down_url(audio_chapter) %}
    <a target="_blank" href="{{ audio_chapter.file_url }}">点击下载</a>
{% endmacro %}

共{{ audio_chapters.total_entries }}个

{{ simple_table(audio_chapters, ["ID": 'id', "名称": 'name','下载':'down_url',"有效": 'status_text', "排名": 'rank', '编辑': 'edit_link']) }}

<script type="text/template" id="audio_chapter_tpl">
    <tr id="audio_chapter_${ audio_chapter.id }">
        <td>${audio_chapter.id}</td>
        <td>${audio_chapter.name}</td>
        <td><a target="_blank" href="${ audio_chapter.file_url }">点击下载</a></td>
        <td>${audio_chapter.status_text}</td>
        <td>${audio_chapter.rank}</td>
        <td><a href="/admin/audio_chapters/edit/${audio_chapter.id}" class="modal_action">编辑</a></td>
    </tr>
</script>