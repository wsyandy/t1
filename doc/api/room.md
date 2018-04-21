# 房间频道

注：

1. 扬声器控制：使用用户基本信息里speaker字段；

2. 话筒控制：由用户角色，用户microphone和麦位microphone字段控制；
2.1. 用户是旁听角色，话筒关闭；
2.2. 用户是房主角色，判断用户microphone字段；
2.3. 用户是主播角色，先判断麦位microphone字段，再判断用户microphone字段；

3. 角色：0不在房间，1房主，2主播，3旁听；


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
    id: int 房间id,
    uid: int 房间uid,
    name: string 房间名称
    channel_name: string 房间唯一标识, 频道名称      
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

### 5.1 进入房间

> http-post ```/api/rooms/enter```

##### 5.1.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|是|进入指定房间
|password|房间密码|string|是|房间密码
|user_id|用户id|int|是|进入指定用户所在房间

##### 5.1.2 回应参数说明
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

### 5.2 房间详情(进入房间里拉取详情)

> http-get ```/api/rooms/detail```

##### 5.2.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|||

##### 5.2.2 回应参数说明
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
    signaling_key string token
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
     },
      menu_config : [ //房间详情菜单配置
             {
                 show boolean 是否展示 true/false
                 title string 菜单名称
                 url string 跳转地址
                 icon string 图片地址
             },
             ...
         ]	   
     "activities": [
             {
                 "id": int 活动ID,
                 "image_small_url": '',
                 "url": string 跳转地址
             }
             ...
         ],
     "game": {
             "url": string  跳转地址,
             "icon": string 图片地址
         },
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
|id|房间id|int|否||
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
|id|房间id|int|否|||

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
|id|房间id|int|否|||

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
|id|房间id|int|否|无
|room_seat_id|麦位id|int|是|报用户上麦时上传
|page|页码|int|否|无
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
            room_id  int 用户创建的房间的id，无房间为0
            current_room_id 用户当前所在房间id ,不在房间为0
            current_room_seat_id 用户当前所在麦位id 
            user_role 用户角色 0无角色, 5房主，10管理员, 15主播，20旁听
            monologue 个性签名
            age 年龄  
            level 用户等级
           segment 段位 starshine星耀 king王者 diamond钻石 platinum铂金 gold黄金 silver白银 bronze青铜
            (例:星耀1 starshine1;星耀王者2 king2)
            segment_text 段位文本 星耀1
        },
        ...
    ]
}
```

### 12 房间列表

> http-get ```/api/rooms```

##### 12.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|page|页码|int|否||
|per_page|每页|int|否||
|hot|热门|int|是|1表示请求热门房间|

##### 12.2 回应参数说明
```
{
    error_code
    error_reason
    rooms:[
        {
            id: int 房间id,
            uid: int 房间uid,展示的唯一标识
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

### 13 踢出房间

> http-post ```/api/rooms/kicking```

##### 13.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否||
|user_id|用户id|int|否|被踢用户|

##### 13.2 回应参数说明
```
{
    error_code
    error_reason
    id: int 房间id,
    name: string 房间名称
    channel_name: string 房间唯一标识, 频道名称
}
```

### 14 禁言用户

> http-post ```/api/rooms/close_user_chat```

##### 14.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否||
|user_id|用户id|int|否|||

##### 14.2 回应参数说明
```
{
    error_code
    error_reason
}
```

### 15 解言用户

> http-post ```/api/rooms/open_user_chat```

##### 15.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否||
|user_id|用户id|int|否|||

##### 15.2 回应参数说明
```
{
    error_code
    error_reason
}
```

### 16 房间基本信息

> http-get ```/api/rooms/basic_info```

##### 16.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|是|房间id

##### 16.2 回应参数说明
```
{
    error_code
    error_reason
    id: int 房间id,
    name: string 房间名称
    channel_name: string 房间唯一标识, 频道名称
    lock boole加锁状态, true是加锁
       
}
```

### 17 添加管理员

> http-post ```/api/rooms/add_manager```

##### 17.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|房间id
|duration|管理时长|int|否|-1:永久,1:1小时,3:3小时,24:24小时
|user_id|用户id|int|否|被添加管理员的用户id

##### 17.2 回应参数说明
```
{
    error_code
    error_reason
    user_id int 用户的ID
    is_permanent boolean 是否为永久管理员 true/false
    deadline int 管理员管理时长的截止时间戳 1517319489     
}
```

### 18 删除管理员

> http-post ```/api/rooms/delete_manager```

##### 18.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|房间id
|user_id|用户id|int|否|被删除管理员的用户id

##### 18.2 回应参数说明
```
{
    error_code
    error_reason     
}
```

### 19 更新管理员信息

> http-post ```/api/rooms/update_manager```

##### 19.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|房间id
|duration|添加时长|int|否|1:1小时,3:3小时,24:24小时
|user_id|用户id|int|否|管理员的用户id

##### 19.2 回应参数说明
```
{
    error_code
    error_reason     
    user_id int 用户的ID
    is_permanent boolean 是否为永久管理员 true/false
    deadline int 管理员管理时长的截止时间戳 1517319489     
}
```

### 20 管理员用户列表

> http-get ```/api/rooms/managers```

##### 20.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|房间id

##### 20.2 回应参数说明
```
{
    error_code
    error_reason
    managers:[
        {
            user_id int 用户的ID
            sex int 性别  0:女 1:男
            avatar_url string 正常图像
            avatar_small_url string 小尺寸图像
            nickname string 昵称
            is_permanent boolean 是否为永久管理员 true/false
            deadline int 管理员管理时长的截止时间戳 1517319489
        },
        ...
    ]     
}
```

### 21 更换主题
> http-post ```/api/rooms/set_theme```

##### 21.1 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间ID|int|否||
|room_theme_id|主题ID|int|否|||

##### 21.2 返回参数说明
```
{
    error_code: 0/-1
    error_reason: '返回码说明'
    theme_image_url: string 背景图
}
```

### 22 关闭主题
> http-post ```/api/rooms/close_theme```

##### 22.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间ID|int|否|||

##### 22.2 返回参数说明
```
{
    error_code: 0/-1
    error_reason: '返回码说明'
}
```

### 23 异常退出房间

> http-post ```/api/rooms/offline```

##### 23.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否||
|user_id|离开房间用户的id|int|否|||

##### 23.2 回应参数说明
```
{
    error_code
    error_reason
}
```

### 24 房间类型列表  

> http-get ```/api/rooms/types```

##### 24.1 请求参数说明

```
公共参数
```


##### 24.2 回应参数说明
```
{
    error_code
    error_reason
    types:[
        {
            name string 热门
            type string hot  请求房间列表是传此参数
            value string 1   请求房间列表时type的值
        }
    ]
}
```
### 25 发公屏送消息 

> http-post ```/api/rooms/send_message```

##### 25.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|room_id|房间id|int|否||
|content|消息内容|string|否||
|content_type|消息类型|string|否|文本类型:text|

##### 25.2 返回参数说明
```
{
    error_code: 0/-1
    error_reason: ''
   
}
```

### 26 房间贡献排行榜 

> http-get ```/m/rooms/wealth_rank_list``` 直接跳转

##### 26.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|room_id|房间id|int|否|||

### 27 热搜关键词
> http-get ```/api/rooms/hot_search``` 

##### 27.1 请求参数说明
```
公共参数
```

##### 27.2 回应参数说明
```
{
    error_code: 0/-1
    error_reason: ''
    keywords:[
        '球球',
        '王者',
        '绝地',
        ...
    ]
}
```

### 28 搜索房间

> http-get ```/api/rooms/search```

##### 28.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|page|页码|int|否||
|per_page|每页|int|否||
|type|房间类型|string|是|产品菜单接口返回的参数|
|keyword|关键词|string|是|||



##### 28.2 回应参数说明
```
{
    error_code
    error_reason
    rooms:[
        {
            id: int 房间id,
            uid: int 房间uid,展示的唯一标识
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

### 29 热门房间

> http-get ```/api/rooms/hot```

##### 29.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|top||置顶房间|int|是|
|hot|热门房间|int|是|||
|gang_up|开黑房间|1|是|||
|gang_up_category|开黑分类|1|是|||

##### 29.2 回应参数说明
```
{
    error_code
    error_reason
    top_rooms:[
        {
            id: int 房间id,
            uid: int 房间uid,展示的唯一标识
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
    ],
    
  hot_rooms:[
          {
              id: int 房间id,
              uid: int 房间uid,展示的唯一标识
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
      ],
      
  gang_up_rooms:[  开黑房间
            {
                id: int 房间id,
                uid: int 房间uid,展示的唯一标识
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
        ],
        
  gang_up_category:[ 开黑分类
    {
        name string 分类名称
        type 分类类型
        image_small_url 分类图片
    }
  ]      
        
}
```

### 30 感兴趣的房间

> http-get ```/api/rooms/recommend```

##### 30.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|page|页码|int|否||
|per_page|每页|int|否||

##### 30.2 回应参数说明
```
{
    error_code
    error_reason
    rooms:[
        {
            id: int 房间id,
            uid: int 房间uid,展示的唯一标识
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