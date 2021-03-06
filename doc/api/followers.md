# 关注接口

### 1 关注列表接口

> http-get ```/api/followers```

##### 1.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| page |第几页|int|否|传递的页数|
| per_page |每页的条数|int|是|每页的数据条数，默认10|

##### 1.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    current_page  当前页数
    total_page   总页数
    total_entries   总记录数
    follow_num 关注人的个数
    users:[
        {
            id int 对方用户的ID
            sex int 性别  0:女 1:男
            avatar_url string 正常图像
            avatar_small_url string 小尺寸图像
            nickname' string 昵称
            created_at_text string 关注时间
            room_id 用户创建房间id ，无房间为0
            current_room_id 用户当前所在房间id ,不在房间为0
            current_room_lock  当前房间加锁状态
            current_room_seat_id 用户当前所在麦位id 
            monologue 个性签名
            level 用户等级
           segment 段位 starshine星耀 king王者 diamond钻石 platinum铂金 gold黄金 silver白银 bronze青铜
            (例:星耀1 starshine1;星耀王者2 king2)
            segment_text 段位文本 星耀1
            friend_note 好友备注名
        } 
    ]
}
```

### 2 被关注列表接口

> http-get ```/api/followers/list```

##### 2.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| page |第几页|int|否|传递的页数|
| per_page |每页的条数|int|是|每页的数据条数，默认10|

##### 2.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    current_page  当前页数
    total_page   总页数
    total_entries   总记录数
    users:[
        {
            id int 对方用户的ID
            sex int 性别  0:女 1:男
            avatar_url string 正常图像
            avatar_small_url string 小尺寸图像
            nickname string 昵称
            created_at_text string 关注时间
            room_id 用户创建房间id ，无房间为0
            current_room_id 用户当前所在房间id ,不在房间为0
            current_room_lock  当前房间加锁状态
            current_room_seat_id 用户当前所在麦位id
            monologue 个性签名
            level 用户等级
           segment 段位 starshine星耀 king王者 diamond钻石 platinum铂金 gold黄金 silver白银 bronze青铜
            (例:星耀1 starshine1;星耀王者2 king2)
            segment_text 段位文本 星耀1
            friend_note 好友备注名
        } 
    ]
}
```

### 3 关注接口

> http-post ```/api/followers```

##### 3.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| user_id |用户id|int|否|||

##### 3.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
}
```

### 4 取消关注接口

> http-delete ```/api/followers```

##### 4.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| user_id |用户id|int|否|||

##### 4.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
}
```