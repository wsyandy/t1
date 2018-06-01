共{{ boom_histories.total_entries }}条记录
<br/>

{{ simple_table(boom_histories, [

    'ID': 'id', '用户UID': 'user_uid', '房间ID':'room_id', '引爆者用户id':'boom_user_id','贡献值':'pay_amount','礼物': 'gift_name', '类型': 'type_text', '数量': 'number','爆礼物值':'boom_amount',
    '爆礼物次数':'boom_num','中奖金额':'amount','创建时间': 'created_at_text'

]) }}
