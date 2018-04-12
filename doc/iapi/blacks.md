# 拉黑接口

### 1 黑名单列表接口

> http-get ```/iapi/blacks```

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
    users:[
        {
           id int 对方用户的ID
           sex int 性别  0:女 1:男
           avatar_url string 正常图像
           avatar_small_url string 小尺寸图像
           nickname string 昵称
           created_at_text string 拉黑时间
           room_id 用户创建房间id ，无房间为0
           current_room_id 用户当前所在房间id,不在房间为0 
           current_room_seat_id 用户当前所在麦位id 
        } 
    ]
}
```

### 2 拉黑接口

> http-post ```/iapi/blacks```

##### 2.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| user_id |用户id|int|否|||

##### 2.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
}
```

### 4 取消拉黑接口

> http-delete ```/iapi/blacks```

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