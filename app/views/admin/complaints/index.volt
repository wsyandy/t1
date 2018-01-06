{{ simple_table(complaints, [
    'ID': 'id', '时间': 'created_at_text', '举报人':'complainer_nickname','被举报人': 'respondent_nickname',
    '房间': 'room_name','举报类型': 'complaint_type_text',
    '状态': 'status_text'
]
) }}

