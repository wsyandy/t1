共{{ backpacks.total_entries }}条记录
<br/>

<a href="/admin/backpacks/give_backpacks?user_id={{ user_id }}" class="modal_action">赠送背包</a>


{{ simple_table(backpacks, [
'ID': 'id', '用户ID': 'user_id', '礼物ID': 'target_id','类型': 'type', '状态': 'status',
'数量': 'number', '创建时间': 'created_at_text',
'更新时间': 'updated_at_text'
]) }}
