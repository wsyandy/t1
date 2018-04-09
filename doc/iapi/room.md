# 房间频道

注：

1. 扬声器控制：使用用户基本信息里speaker字段；

2. 话筒控制：由用户角色，用户microphone和麦位microphone字段控制；
2.1. 用户是旁听角色，话筒关闭；
2.2. 用户是房主角色，判断用户microphone字段；
2.3. 用户是主播角色，先判断麦位microphone字段，再判断用户microphone字段；

3. 角色：0不在房间，1房主，2主播，3旁听；

### 1 房间列表

> http-get ```/iapi/rooms```

##### 1.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|page|页码|int|否||
|per_page|每页|int|否||
|hot|热门|int|是|1表示请求热门房间|
|new|最新|int|是|1表示请求最新房间|

##### 1.2 回应参数说明
```
{
    error_code
    error_reason
    rooms:[
        {
            id: int 房间id,
            uid: int 房间uid,
            name: string 房间名称
            topic: string 房间话题
            chat: 公屏聊天状态, false/true
            user_id 房主用户id
            sex	性别 0:女 1:男
            avatar_small_url 房主小头像
            avatar_url 房主头像原图
            avatar_big_url 房主头像大图
            nickname 房主昵称
            age int 年龄
            monologue 个性签名
            online_status 0离线，1在线
            channel_name: string 房间唯一标识, 频道名称
            lock boole加锁状态, true是加锁
            created_at int 创建时间戳
            last_at int 最后活跃时间
            user_num 在线人数
        }
         ....
    ]
}
```


### 创建房间(创建后默认进入房间)

> http-post ```/iapi/rooms/create```

##### 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---|
|name|房间名称|string|否|||

##### 回应参数说明
```
{
    error_code
    error_reason  
    id: int 房间id,
    uid: int 房间uid,
    name: string 房间名称
    channel_name: string 房间唯一标识, 频道名称      
}
```


### 更新房间信息

> http-post ```/iapi/rooms/update```

##### 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否||
|name|房间名称|string|是||
|topic|房间话题|string|是|||

##### 回应参数说明
```
{
    error_code
    error_reason	    
}
```

### Signaling Key用于登录(信令系统)

> http-get ```/iapi/rooms/signaling_key```

##### 请求参数说明
无

##### 回应参数说明
```
{
    error_code
    error_reason
    app_id string 应用id
    signaling_key string token
}
```

### Channel Key 用于加入频道(直播系统)

> http-get ```/iapi/rooms/channel_key```

##### 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|||

##### 回应参数说明
```
{
    error_code
    error_reason
    app_id string 应用id
    channel_key string token
}
```

### 进入房间

> http-post ```/iapi/rooms/enter```

##### 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|是|进入指定房间
|password|房间密码|string|是|房间密码
|user_id|用户id|int|是|进入指定用户所在房间

##### 回应参数说明
```
{
    error_code,0成功,-1参数错误,-400密码错误
    error_reason
    id: int 房间id,
    uid: int 房间uid,
    name: string 房间名称
    channel_name: string 房间唯一标识, 频道名称
}
```

###  房间详情(进入房间里拉取详情)

> http-get ```/iapi/rooms/detail```

#####  请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|||

#####  回应参数说明
```
{
    error_code,
    error_reason：,
    id: int 房间id,
    uid: int 房间uid,
    name: string 房间名称
    topic: string 房间话题
    chat: 房间公屏聊天状态, false/true
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
    app_id string 应用id
    channel_key string token 用于加入频道(互动直播)
    user_chat boolean 当前用户是否可以发公屏消息 true可以false不可以
    user_role 当前用户角色 0无角色, 5房主，10管理员, 15主播，20旁听
    theme_type 房间主题类型 0普通房间 1电台房间 如果是电台类型 需要请求接口拉取音频资源
    audio_id 房间音频id 拉取音频资源时使用
    theme_image_url string 房间主题背景图
    room_theme_id int 房间主题id
    system_tips:[
        '内容1',
        '内容2',
        '内容3',
    ]  公屏系统消息
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
            rank 麦位排序, 1-8, 8个麦位
            can_play_music 能否播放音乐 true/false 默认为false
        }
        ...
    ]
     managers:[
            {
                id int 用户的ID
                sex int 性别  0:女 1:男
                avatar_url string 正常图像
                avatar_small_url string 小尺寸图像
                nickname string 昵称
                is_permanent boolean 是否为永久管理员 true/false
                deadline int 管理员管理时长的截止时间戳 1517319489
            },
            ...
        ]  
     user_car_gift: {
        name: ''
        image_url: ''
        image_small_url: ''
        image_big_url: ''
        dynamic_image_url: ''
        svga_image_name: svga 对应zip包中的图片名称
        render_type 渲染类型 gif svga
        svga_image_url svga 图片
        show_rank int 礼物展示排序
        expire_time int 礼物过期时间
        gift_type 1 普通礼物 2 座驾
        notice_content 进房间提示文案
     }      		   
}
```