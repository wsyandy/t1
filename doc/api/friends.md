# 好友接口

### 1 好友列表接口

> http-get ```/api/friends```

##### 1.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| page |第几页|int|否|当前页码|
| per_page |每页的条数|int|是|每页的数据条数（默认10）|
| type |状态|int|是|1 好友列表 2 新的好友列表（默认1）|

##### 1.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason
    current_page  当前页数
    total_page   总页数
    total_entries   总记录数
    users:{
          'uid' int 对方用户的ID
          'sex' int 性别  0:女 1:男
          'avatar_url' string 正常图像
          'avatar_small_url' string 小尺寸图像
          'login_name' string 用户名
          'nickname' string 昵称
          'created_at_text' int 创建时间
          'room_id'  int 用户所在房间的ID
          'friend_status' int 好友状态 1已添加,2等待验证，3等待接受
          'friend_status_text' string 好友状态名称
          } 
}
```

### 2 添加好友接口

> http-post ```/api/friends```

##### 2.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| uid |用户id|int|否|||

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
| uid |用户id|int|否|||

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
| uid |用户id|int|否|||

##### 4.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
}
```

### 5 清空新的朋友信息接口

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