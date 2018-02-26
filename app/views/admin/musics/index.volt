<a href="/admin/musics/new" class="modal_action">新增</a>


{%- macro edit_link(music) %}
    <a href="/admin/musics/edit/{{ music.id }}" class="modal_action">编辑</a>
{%- endmacro %}



{{ simple_table(musics, [
"ID": 'id', "名称": 'name',"歌手名称": 'singer_name', '类型':'type_text',"有效": 'status_text', "排名": 'rank','编辑': 'edit_link'
]) }}

<script type="text/template" id="music_tpl">
    <tr id="audio_${ music.id }">
        <td>${music.id}</td>
        <td>${music.name}</td>
        <td>${music.singer_name}</td>
        <td>${music.type_text}</td>
        <td>${music.status_text}</td>
        <td>${music.rank}</td>
        <td><a href="/admin/musics/edit/${music.id}" class="modal_action">编辑</a></td>
    </tr>
</script>