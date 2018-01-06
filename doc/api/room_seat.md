# 房间麦位

### 1 上麦或抱用户上麦

> http-post ```/api/room_seats/up```

##### 1.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否||
|user_id|用户id|int|是|自己上麦可为空，抱用户上麦不为空

##### 1.2 回应参数说明
```
{
    error_code
    error_reason
    user_id 用户id
    sex	性别 0:女 1:男
    avatar_url 用户头像
    avatar_small_url 用户小头像
    nickname 昵称
    im_password 即时通信登录密码
    room_id 用户创建房间id，无房间为0 
    current_room_id 用户当前所在房间id,不在房间为0
    current_room_seat_id 用户当前所在麦位id
    user_role 当前用户角色，无角色，房主，主播，旁听
    speaker 扬声器状态 false/true 默认为true
    microphone 麦克风状态 false/true 默认为true
    }
}
```


### 2 下麦或设为旁听 

> http-post ```/api/room_seats/down```

##### 2.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否||
|user_id|用户id|int|是|自己下麦可为空，设为旁听不为空

##### 2.2 回应参数说明
```
{
    error_code
    error_reason
    user_id 用户id
    sex	性别 0:女 1:男
    avatar_url 用户头像
    avatar_small_url 用户小头像
    nickname 昵称
    im_password 即时通信登录密码
    room_id 用户创建房间id，无房间为0 
    current_room_id 用户当前所在房间id,不在房间为0
    current_room_seat_id 用户当前所在麦位id
    user_role 当前用户角色，无角色，房主，主播，旁听
    speaker 扬声器状态 false/true 默认为true
    microphone 麦克风状态 false/true 默认为true
}
```

### 3 封麦

> http-post ```/api/room_seats/close```

##### 3.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否|||

##### 3.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```

### 4 解封麦位

> http-post ```/api/room_seats/open```

##### 4.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否|||

##### 4.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```

### 5 解除禁麦

> http-post ```/api/room_seats/open_microphone```

##### 5.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否|||

##### 5.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```

### 6 禁麦

> http-post ```/api/room_seats/close_microphone```

##### 6.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否|||

##### 6.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```