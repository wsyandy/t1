# 房间频道

注：扬声器控制：使用用户基本信息里speaker字段

### 1 创建房间(创建后默认进入房间)

> http-post ```/api/rooms/create```

##### 1.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---|
|name|房间名称|string|否|||

##### 1.2 回应参数说明
```
{
		    error_code
		    error_reason
            room:{
                id: int 房间id,
                name: string 房间名称
                topic: string 房间话题
                chat: 公屏聊天状态, false/true
                user_id 房主用户id
                sex	性别 0:女 1:男
                avatar_small_url 用户小头像
                nickname 昵称
                online_status 0离线，1在线
                channel_name: string 房间唯一标识, 频道名称
                lock boole加锁状态, true是加锁
                created_at int 创建时间戳
                last_at int 最后活跃时间
                user_num 在线人数,
                room_seats:[
                    {
                      id: int 麦位id,
                      user_id 麦位主播id，无主播为0
                      sex	性别 0:女 1:男
                      avatar_small_url 用户小头像
                      nickname 昵称
                      room_id 房间id
                      status: int 麦位状态，0 麦为被封，1 麦位正常
                      microphone 麦克风状态 false/true 默认为true,
                      rank 麦位排序, 0-8, 0是房主麦位
                    }
                    ...
                ]
            }
}
```

### 2 更新房间信息

> http-post ```/api/rooms/update```

##### 2.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否||
|name|房间名称|string|是||
|topic|房间话题|string|是|||

##### 2.2 回应参数说明
```
{
		    error_code
		    error_reason
		    
}
```


### 3 Signaling Key用于登录(信令系统)

> http-get ```/api/rooms/signaling_key```

##### 3.1 请求参数说明
无

##### 3.2 回应参数说明
```
{
		    error_code
		    error_reason
		    app_id string 应用id
		    signaling_key string token
}
```


### 4 Channel Key 用于加入频道(直播系统)

> http-get ```/api/rooms/channel_key```

##### 4.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|||

##### 4.2 回应参数说明
```
{
		    error_code
		    error_reason
		    app_id string 应用id
            channel_key string token
}
```

### 5 进入房间

> http-post ```/api/rooms/enter```

##### 5.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否||
|password|房间密码|string|是|房间密码

##### 5.2 回应参数说明
```
{
		    error_code,
		    error_reason：,
            room:{
                id: int 房间id,
                name: string 房间名称
                topic: string 房间话题
                chat: 公屏聊天状态, false/true
                user_id 房主用户id
                sex	性别 0:女 1:男
                avatar_small_url 用户小头像
                nickname 昵称
                online_status 0离线，1在线
                channel_name: string 房间唯一标识, 频道名称
                lock boole加锁状态, true是加锁
                created_at int 创建时间戳
                last_at int 最后活跃时间
                user_num 在线人数
                room_seats:[
                    {
                      id: int 麦位id,
                      user_id 麦位主播id，无主播为0
                      sex	性别 0:女 1:男
                      avatar_small_url 用户小头像
                      nickname 昵称
                      room_id 房间id
                      status: int 麦位状态，0 麦为被封，1 麦位正常
                      microphone 麦克风状态 false/true 默认为true,
                      rank 麦位排序, 0-8, 0是房主麦位
                    }
                    ...
                ]
            }
		   
}
```

### 6 退出房间

> http-post ```/api/rooms/exit```
如果是主播需要处理下麦

##### 6.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|||

##### 6.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```

### 7 房间加锁

> http-post ```/api/rooms/lock```

##### 7.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|||
|password|密码|string|否|||

##### 7.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```

### 8 房间解锁

> http-post ```/api/rooms/unlock```

##### 8.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|||

##### 8.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```

### 9 打开公屏聊天

> http-post ```/api/rooms/open_chat```

##### 9.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否||

##### 9.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```

### 10 关闭公屏聊天

> http-post ```/api/rooms/close_chat```

##### 10.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否||

##### 10.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```


### 11 房间用户列表

> http-get ```/api/rooms/users```

##### 11.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否||
|page|页码|int|否||
|per_page|每页个数|int|否|默认8个

##### 11.2 回应参数说明
```
{
		    error_code,
		    error_reason,
		    users:[
		        {
		           id int 用户的ID
                   sex int 性别  0:女 1:男
                   avatar_url string 正常图像
                   avatar_small_url string 小尺寸图像
                   nickname string 昵称
                   room_id  int 用户所在房间的ID
                   room_seat_id 麦位id 
                   user_role 用户角色 0无角色, 1房主，2主播，3旁听  
		        },
		        ...
		    ]
}
```


### 12 设置扬声器

> http-post ```/api/users/set_speaker```

##### 12.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|speaker|扬声器|boole|否|||

##### 12.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```


### 13 设置麦克风

> http-post ```/api/users/set_microphone```

##### 13.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|microphone|麦克风|boole|否|||

##### 13.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```

### 14 房间列表

> http-get ```/api/rooms```

##### 14.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---|
|page|页码|int|否|||
|per_page|每页|int|否|||

##### 14.2 回应参数说明
```
{
		    error_code
		    error_reason
            rooms:[
                {
                    id: int 房间id,
                    name: string 房间名称
                    topic: string 房间话题
                    chat: 公屏聊天状态, false/true
                    user_id 房主用户id
                    sex	性别 0:女 1:男
                    avatar_small_url 房主小头像
                    nickname 房主昵称
                    online_status 0离线，1在线
                    channel_name: string 房间唯一标识, 频道名称
                    lock boole加锁状态, true是加锁
                    created_at int 创建时间戳
                    last_at int 最后活跃时间
                }
                 ....
            ]
}
```