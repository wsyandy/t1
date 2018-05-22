# 房间麦位

### 1 上麦或抱用户上麦

> http-post ```/api/room_seats/up```

##### 1.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否||
|user_id|用户id|int|是|自己上麦为空，抱用户上麦不为空

##### 1.2 回应参数说明
```
{
    error_code
    error_reason
    id 麦位id
    user_id 麦位主播id 无主播无此字段
    sex	性别 0:女 1:男 无主播无此字段
    avatar_url 用户头像 无主播无此字段
    avatar_small_url 用户小头像 无主播无此字段
    avatar_100x100_url 100x100头像
    avatar_60x60_url 60x60头像
    nickname 昵称 无主播无此字段
    room_id 房间id
    microphone 麦位麦克风状态 false/true 默认为true
    rank 麦位排序, 1-8， 8个麦位
    status int 麦位状态 0 麦位被封 1麦位正常
    can_play_music 能否播放音乐 true/false 默认为false
}
```


### 2 下麦或设为旁听 

> http-post ```/api/room_seats/down```

##### 2.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否||
|user_id|用户id|int|是|自己下麦为空，设为旁听不为空

##### 2.2 回应参数说明
```
{
    error_code
    error_reason
    id 麦位id
    room_id 房间id
    microphone 麦位麦克风状态 false/true 默认为true
    rank 麦位排序, 1-8， 8个麦位
    status int 麦位状态 0 麦位被封 1麦位正常
    can_play_music 能否播放音乐 true/false 默认为false
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
    id 麦位id
    room_id 房间id
    microphone 麦位麦克风状态 false/true 默认为true
    rank 麦位排序, 1-8， 8个麦位
    status int 麦位状态 0 麦位被封 1麦位正常
    can_play_music 能否播放音乐 true/false 默认为false
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
    id 麦位id
    room_id 房间id
    microphone 麦位麦克风状态 false/true 默认为true
    rank 麦位排序, 1-8， 8个麦位
    status int 麦位状态 0 麦位被封 1麦位正常
    can_play_music 能否播放音乐 true/false 默认为false
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
    id 麦位id
    user_id 麦位主播id 无主播无此字段
    sex	性别 0:女 1:男 无主播无此字段
    avatar_url 用户头像 无主播无此字段
    avatar_small_url 用户小头像 无主播无此字段
    nickname 昵称 无主播无此字段
    room_id 房间id
    microphone 麦位麦克风状态 false/true 默认为true
    rank 麦位排序, 1-8， 8个麦位
    status int 麦位状态 0 麦位被封 1麦位正常
    can_play_music 能否播放音乐 true/false 默认为false
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
    id 麦位id
    user_id 麦位主播id 无主播无此字段
    sex	性别 0:女 1:男 无主播无此字段
    avatar_url 用户头像 无主播无此字段
    avatar_small_url 用户小头像 无主播无此字段
    nickname 昵称 无主播无此字段
    room_id 房间id
    microphone 麦位麦克风状态 false/true 默认为true
    rank 麦位排序, 1-8， 8个麦位
    status int 麦位状态 0 麦位被封 1麦位正常
    can_play_music 能否播放音乐 true/false 默认为false
}
```
### 7 确认上麦

> http-post ```/api/room_seats/confirm_up```

##### 7.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否|||

##### 7.2 回应参数说明
```
{
    error_code
    error_reason
}
```


### 8 取消上麦 

> http-post ```/api/room_seats/cancel_up```

##### 8.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否|||

##### 8.2 回应参数说明
```
{
    error_code
    error_reason
    id 麦位id
    room_id 房间id
    microphone 麦位麦克风状态 false/true 默认为true
    rank 麦位排序, 1-8， 8个麦位
    status int 麦位状态 0 麦位被封 1麦位正常
    can_play_music 能否播放音乐 true/false 默认为false
}
```

### 9 打开音乐权限 

> http-post ```/api/room_seats/open_music_permission```

##### 9.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否|||

##### 9.2 回应参数说明
```
{
    error_code
    error_reason
    id 麦位id
    room_id 房间id
    microphone 麦位麦克风状态 false/true 默认为true
    rank 麦位排序, 1-8， 8个麦位
    status int 麦位状态 0 麦位被封 1麦位正常
    can_play_music 能否播放音乐 true/false 默认为false
}
```

### 10 关闭音乐权限 

> http-post ```/api/room_seats/close_music_permission```

##### 10.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否|||

##### 10.2 回应参数说明
```
{
    error_code
    error_reason
    id 麦位id
    room_id 房间id
    microphone 麦位麦克风状态 false/true 默认为true
    rank 麦位排序, 1-8， 8个麦位
    status int 麦位状态 0 麦位被封 1麦位正常
    can_play_music 能否播放音乐 true/false 默认为false
}
```

### 11 麦位详情

> http-get ```/api/room_seats/detail```

##### 11.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否|||

##### 11.2 回应参数说明
```
{
    error_code,
    error_reason：,
    id: int 麦位id,
    user_id 麦位主播id，无主播为0
    sex	性别 0:女 1:男
    avatar_small_url 用户小头像
    avatar_100x100_url 100x100头像
    avatar_60x60_url 60x60头像
    nickname 昵称
    room_id 房间id
    status: int 麦位状态，0 麦为被封，1 麦位正常
    microphone 麦克风状态 false/true 默认为true,
    rank 麦位排序, 1-8, 8个麦位
    can_play_music 能否播放音乐 true/false 默认为false
}
```
