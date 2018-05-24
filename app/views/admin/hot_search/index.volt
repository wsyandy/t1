<a href="/admin/hotSearch/new" class="modal_action">新增</a>



<table class="table table-striped table-condensed table-hover">
    <thead>
        <tr>
            <th id="column_weight" data-field="weight">权重</th>
            <th id="column_word" data-field="word">热搜词</th>
            <th id="column_edit_link">编辑</th>
        </tr>
    </thead>
    <tbody id="hs_list">
        {% for item in hot_search %}
        <tr id="hs_">
            <td>{{ item['weight'] }}</td>
            <td>{{ item['word'] }}</td>
            <td><a href="/admin/hotSearch/edit/{{ item['weight'] }}" class="modal_action">编辑</a></td>
        </tr>
        {% endfor %}
    </tbody>
</table>


<script type="text/template" id="gift_tpl">
    <tr id="ht_">
        <td>${hot_search.weight}</td>
        <td>${hot_search.word}</td>
        <td><a href="/admin/gifts/edit/${hot_search.weight}" class="modal_action">编辑</a></td>
    </tr>
</script>
