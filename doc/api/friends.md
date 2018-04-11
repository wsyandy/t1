# 好友接口

### 1 好友列表接口

> http-get ```/api/friends```

##### 1.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| page |第几页|int|否|当前页码|
| per_page |每页的条数|int|是|每页的数据条数（默认10）|
| new |新的朋友|int|是|1:新的好友列表, 默认为0|

##### 1.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason
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
            monologue string 个性签名
            created_at_text string 创建时间
            room_id 用户创建房间id ，无房间为0
            current_room_id 用户当前所在房间id ,不在房间为0
            current_room_seat_id 用户当前所在麦位id
            current_room_lock  当前房间加锁状态
            friend_status int 好友状态 1已添加,2等待验证，3等待接受
            friend_status_text string 好友状态名称
            self_introduce 自我介绍
            level 用户等级
           segment 段位 starshine星耀 king王者 diamond钻石 platinum铂金 gold黄金 silver白银 bronze青铜
            (例:星耀1 starshine1;星耀王者2 king2)
            segment_text 段位文本 星耀1
        }
    ] 
    friend_num 好友人数
    new_friend_num 新好友人数
}
```

### 2 添加好友接口

> http-post ```/api/friends```

##### 2.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| user_id |用户id|int|否||
| self_introduce |自我介绍|string|是|客户端需限制文字的长度|

##### 2.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason
}
```

### 3 删除好友接口

> http-delete ```/api/friends```

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

### 4 同意添加好友接口

> http-post ```/api/friends/agree```

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

### 5 清空新的朋友接口

> http-post ```/api/friends/clear```

##### 5.1 请求参数说明
无

##### 5.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
}
```

### 6 拒绝添加好友接口

> http-post ```/api/friends/refuse```

##### 6.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| user_id |用户id|int|否||

##### 6.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason
}
```