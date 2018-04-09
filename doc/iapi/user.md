# 用户接口

### 注册接口

> http-post ```/iapi/users/register```

#####  请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|login_name|注册邮箱|string|否||
|password|密码|string|否||
|country_id|国家id|int|否|||

##### 回应参数说明
```
{
    error_code
    error_reason  失败原因，默认为空
    error_url 跳转地址
    sid 身份信息 必须先更新本地SID
    id 用户id，即时通信账户(声网，环信)
    im_password 即时通信密码(声网，环信)
    app_id string 信令应用id
    signaling_key string 信令token
}

```

### 登录接口

> http-post ```/iapi/users/login```

##### 手机号码登录
###### 参数
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|login_name|登录邮箱|string|否||
|password|密码|string|否||
|country_id|国家id|int|否|||

###### 回应参数说明
```
{
    error_code
    error_reason  失败原因，默认为空
    error_url 跳转地址
    sid 身份信息 必须先更新本地SID
    id 用户id，即时通信账户(声网，环信)
    im_password 即时通信密码(声网，环信)
    sex	性别 0:女 1:男
    province_name 省名
    city_name 城市
    avatar_url 用户头像
    avatar_small_url 用户小头像
    nickname 昵称
    room_id 用户创建房间id，无房间为0 
    current_room_id 用户当前所在房间id,不在房间为0
    current_room_seat_id 用户当前所在麦位id
    user_role 当前用户角色，无角色，房主，主播，旁听
    mobile 手机号
    speaker 扬声器状态 false/true 默认为true
    app_id string 信令应用id
    signaling_key string 信令token
    country_id  国家id
    country_english_name 国家英文名称
    country_chinese_name 国家中文名称
}

```
### 退出登陆

> http-get ```/iapi/users/logout```

##### 请求参数说明
无

##### 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    sid : 设备的SID   123d......
}
```

### 用户基本信息

> http-get ``` /iapi/users/basic_info```

##### 请求参数说明

##### 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    id 用户id
    sex	性别 0:女 1:男
    country_id  国家id
    country_english_name 国家英文名称
    country_chinese_name 国家中文名称
    province_name 省名
    city_name 城市
    avatar_url 用户头像
    avatar_small_url 用户小头像
    nickname 昵称
    im_password 即时通信登录密码
    room_id 用户创建房间id，无房间为0 
    current_room_id 用户当前所在房间id,不在房间为0
    current_room_seat_id 用户当前所在麦位id
    user_role 当前用户角色，无角色，房主，主播，旁听
    mobile 手机号
    speaker 扬声器状态 false/true 默认为true
    followed_num 粉丝人数
    follow_num 关注人数,
    level 用户等级
    segment 段位 starshine星耀 king王者 diamond钻石 platinum铂金 gold黄金 silver白银 bronze青铜
    (例:星耀1 starshine1;星耀王者2 king2)
    segment_text 段位文本 星耀1
}
```

### 用户详细信息

> http-get ``` /iapi/users/detail```

##### 请求参数说明

##### 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    id 用户id
    sex	性别 0:女 1:男
    country_id  国家id
    country_english_name 国家英文名称
    country_chinese_name 国家中文名称
    province_name 省名
    city_name 城市
    avatar_url 用户头像
    avatar_small_url 用户小头像
    nickname 昵称
    mobile  string 手机号
    room_id 用户创建房间id ，无房间为0
    current_room_id 用户当前所在房间id ,不在房间为0
    current_room_seat_id 用户当前所在麦位id
    current_channel_name 当前所在房间频道名称
    user_role 当前用户角色，无角色，房主，主播，旁听
    monologue 个人签名
    followed_num 粉丝人数
    follow_num 关注人数,
    friend_num 好友人数
    interests 兴趣爱好
    height 身高
    im_password 即时通信登录密码
    age 年龄
    birthday 生日
    constellation 星座
    user_role 当前用户角色，无角色，房主，主播，旁听
    mobile 手机号
    speaker 扬声器状态 false/true 默认为true
    level 用户等级
    segment 段位 starshine星耀 king王者 diamond钻石 platinum铂金 gold黄金 silver白银 bronze青铜
    (例:星耀1 starshine1;星耀王者2 king2)
    segment_text 段位文本 星耀1
    next_level_experience 下一级经验值
    experience 当前经验值
    union_name 家族名，不存在为空字符
    id_card_auth 主持认证状态  1已认证
    diamond 钻石余额 
    albums:[
        {
            id,
            image_url 原图
            image_small_url 小图
            image_big_url 小图
        }
        ...
    ]
}
```

### 他人用户详细信息

> http-get ``` /iapi/users/other_detail```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|user_id|用户id|int|是|查看他人资料传此参数|

##### 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    id 用户id
    sex	性别 0:女 1:男
    country_id  国家id
    country_english_name 国家英文名称
    country_chinese_name 国家中文名称
    province_name 省名
    city_name 城市
    avatar_url 用户头像
    avatar_small_url 用户小头像
    nickname 昵称
    mobile  string 手机号
    room_id 用户创建房间id ，无房间为0
    current_room_id 用户当前所在房间id ,不在房间为0
    current_room_seat_id 用户当前所在麦位id
    current_channel_name 当前所在房间频道名称 
    user_role 当前用户角色，无角色，房主，主播，旁听
    current_room_lock 房间是否加锁 true/false
    monologue 个人签名
    followed_num 粉丝人数
    follow_num 关注人数,
    friend_num 好友人数
    is_friend true/false,是否是好友
    is_follow true/false,是否已关注
    interests 兴趣爱好
    height 身高
    age 年龄
    birthday 生日
    constellation 星座
    user_role 当前用户角色，无角色，房主，主播，旁听
    mobile 手机号
    speaker 扬声器状态 false/true 默认为true
    level 用户等级
    segment 段位 starshine星耀 king王者 diamond钻石 platinum铂金 gold黄金 silver白银 bronze青铜
    (例:星耀1 starshine1;星耀王者2 king2)
    segment_text 段位文本 星耀1
    receive_gift_num 接受的礼物个数
    next_level_experience 下一级经验值
    experience 当前经验值
    union_name 家族名，不存在为空字符
    id_card_auth 主持认证状态  1已认证
    albums:[
        {
            id,
            image_url 原图
            image_small_url 小图
            image_big_url 小图
        }
        ...
    ]
}
```

### 用户信息更新,完善资料

> http-post ```/iapi/users/update ```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|avatar_file|头像|file|||
|country_id|国家id|int|||
|nickname|昵称|string|||
|sex|性别|int||0:女 1:男|
|province_name|省份名称|string|||
|city_name|城市名称|string|||
|monologue|个性签名|string||客户端需限制字数长度|
|age|年龄|int|||
|height|身高|int|||
|birthday|生日|string||||

##### 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    id 用户id
    sex	性别 0:女 1:男
    country_id  国家id
    country_english_name 国家英文名称
    country_chinese_name 国家中文名称
    province_name 省名
    city_name 城市
    avatar_url 用户头像
    avatar_small_url 用户小头像
    nickname 昵称
    mobile  string 手机号
    room_id 用户创建房间id ，无房间为0
    current_room_id 用户当前所在房间id ,不在房间为0
    current_room_seat_id 用户当前所在麦位id
    current_channel_name 当前所在房间频道名称
    user_role 当前用户角色，无角色，房主，主播，旁听
    monologue 个人签名
    followed_num 粉丝人数
    follow_num 关注人数,
    friend_num 好友人数
    interests 兴趣爱好
    height 身高
    im_password 即时通信登录密码
    age 年龄
    birthday 生日
    constellation 星座
    level 用户等级
    segment 段位 starshine星耀 king王者 diamond钻石 platinum铂金 gold黄金 silver白银 bronze青铜
    (例:星耀1 starshine1;星耀王者2 king2)
    segment_text 段位文本 星耀1
    albums:[
        {
            id,
            image_url 原图
            image_small_url 小图
            image_big_url 小图
        }
        ...
    ]
}
```

### 上传头像

> http-post ```/iapi/users/update_avatar ```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|avatar_file|头像文件|file|否|||

##### 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
}
```

### 更新push_token

> http-post ```/iapi/users/push_token```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|push_token|||||
|push_from||||默认(getui),可以为getui,xinge|

##### 回应参数说明
```
{
    error_code: 0成功，非0失败
    error_reason  失败原因，默认为空
}
```

### 用户搜索接口

> http-post ```/iapi/users/search```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|user_id|用户id|int|是||
|page|当前页|int|是||
|per_page|每页个数|int|是|||

##### 回应参数说明
```
{
    error_code: 0成功，非0失败
    error_reason  失败原因，默认为空
    users:[
        {
             id 用户id
             sex	性别 0:女 1:男
             province_name 省名
             city_name 城市
             avatar_url 用户头像
             avatar_small_url 用户小头像
             nickname 昵称
             room_id 用户创建房间id，无房间为0 
             current_room_id 用户当前所在房间id,不在房间为0
             current_room_seat_id 用户当前所在麦位id
             current_channel_name 当前所在房间频道名称 
             mobile 手机号
             monologue 个性签名
             level 用户等级
            segment 段位 starshine星耀 king王者 diamond钻石 platinum铂金 gold黄金 silver白银 bronze青铜
            (例:星耀1 starshine1;星耀王者2 king2)
            segment_text 段位文本 星耀1
        }
    ]
}
```
### 附近的人

> http-get ```/iapi/users/nearby```

##### 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|page|页码|int|否||
|per_page|每页|int|否|||

##### 回应参数说明
```
{
    error_code
    error_reason
    users:[
        {
             id 用户id
             sex	性别 0:女 1:男
             province_name 省名
             city_name 城市
             avatar_url 用户头像
             avatar_small_url 用户小头像
             nickname 昵称
             room_id 用户创建房间id，无房间为0 
             current_room_id 用户当前所在房间id,不在房间为0
             current_room_seat_id 用户当前所在麦位id
             current_channel_name 当前所在房间频道名称
             current_room_lock 当前房间是否加锁 true/false
             user_role 当前用户角色，无角色，房主，主播，旁听
             mobile 手机号
             monologue 个性签名
             distance string 距离,例如 0.5km
             age 年龄
             level 用户等级
            segment 段位 starshine星耀 king王者 diamond钻石 platinum铂金 gold黄金 silver白银 bronze青铜
            (例:星耀1 starshine1;星耀王者2 king2)
            segment_text 段位文本 星耀1
        }
    ]               
}
```

### 设置扬声器

> http-post ```/iapi/users/set_speaker```

##### 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|speaker|扬声器|boole|否|false是静音

##### 回应参数说明
```
{
    error_code
    error_reason
}
```

### 设置麦克风

> http-post ```/iapi/users/set_microphone```

##### 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|microphone|麦克风|boole|否|false是静音

##### 回应参数说明
```
{
    error_code
    error_reason
}
```



### 我的账户(只对iOS有效)

> http-get ```/iapi/users/account```

##### 请求参数说明
```
公共参数
```

##### 返回参数说明
```
{
    error: 0/-1
    error_reason: ''
    diamond: 100,
    products: [
        {
            id: 1
            name: ''
            amount: 10
            diamond: 100
            apple_product_no: ''
        }
        ...
    ]
}
```

###  第三方登录接口

> http-post ```/iapi/users/third_login```

##### 第三方登录(微信,QQ,新浪微博)
###### 参数
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|third_name|登录方式名称|string|否|微信:weixin,QQ:qq,新浪微博:sinaweibo|
|access_token|登录的token|string|否||
|openid|用户的唯一id|string|否||
|app_id|应用的appid|string|是|QQ登录需要此参数||

###### 回应参数说明
```
{
    error_code
    error_reason  失败原因，默认为空
    error_url 跳转地址
    sid 身份信息 必须先更新本地SID
    id 用户id，即时通信账户(声网，环信)
    im_password 即时通信密码(声网，环信)
    sex	性别 0:女 1:男
    province_name 省名
    city_name 城市
    avatar_url 用户头像
    avatar_small_url 用户小头像
    nickname 昵称
    room_id 用户创建房间id，无房间为0 
    current_room_id 用户当前所在房间id,不在房间为0
    current_room_seat_id 用户当前所在麦位id
    user_role 当前用户角色，无角色，房主，主播，旁听
    mobile 手机号
    speaker 扬声器状态 false/true 默认为true
    app_id string 信令应用id
    signaling_key string 信令token
}
```


### 扫码登录说明

    1. 用户打开登录后台。
    2. 用客户端扫描二维码
    3. 客户端访问二维码链接地址。会返回一个auth_url。
    4. 用户确认登录后再访问auth_url。error_reason 中会返回 确认成功 。到此客户端任务完成。
    5. 登录后台会自动跳转到对应的操作页面

### 我的曲库

> http-get ```/iapi/users/musics```

##### 参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|page|当前页码|int|否||
|per_page|每页个数|int|否|||

##### 回应参数说明
```
{
    error_code
    error_reason  失败原因，默认为空
    error_url 跳转地址
    musics [
        {
            id int 音乐id
            name string 音乐名称
            singer_name string 演唱者名称
            user_name string 上传者
            file_size string 文件大小
            file_size string 文件大小
            down_at int 下载时间
        }
    ]
}
```

### hi币贡献榜
>http-get ```/iapi/users/hi_coin_rank_list```

##### 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|list_type|榜单类型|string|否|day是日榜，week是周榜，total是总榜

##### 回应参数说明
```
{
    error_code
    error_reason
    users:[
        {
            id 用户id
            nickname 昵称
            age 年龄
            sex	性别 0:女 1:男
            avatar_url 用户头像
            avatar_small_url 用户小头像
            rank 排名
            level 用户等级
            segment 段位 starshine星耀 king王者 diamond钻石 platinum铂金 gold黄金 silver白银 bronze青铜
                (例:星耀1 starshine1;星耀王者2 king2)
            segment_text 段位文本 星耀1
            hi_coin string 贡献的hi币 
        }
        ...
    ]
}
```

### 魅力榜
>http-get ```/iapi/users/charm_rank_list```

##### 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|list_type|榜单类型|string|否|day是日榜，week是周榜，total是总榜

##### 回应参数说明
```
{
    error_code
    error_reason
    users:[
        {
            id 用户id
            nickname 昵称
            age 年龄
            sex	性别 0:女 1:男
            avatar_url 用户头像
            avatar_small_url 用户小头像
            rank 排名
            level 用户等级
            segment 段位 starshine星耀 king王者 diamond钻石 platinum铂金 gold黄金 silver白银 bronze青铜
                (例:星耀1 starshine1;星耀王者2 king2)
            segment_text 段位文本 星耀1
            charm_value string 榜单魅力值
        }
        ...
    ]
    current_rank 当前排名
    changed_rank 变化的排名
    current_rank_text string 当前排名描述
}
```
    
### 土豪榜
>http-get ```/iapi/users/wealth_rank_list```

##### 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|list_type|榜单类型|string|否|day是日榜，week是周榜，total是总榜

##### 回应参数说明
```
{
    error_code
    error_reason
    users:[
        {
            id 用户id
            nickname 昵称
            age 年龄
            sex	性别 0:女 1:男
            avatar_url 用户头像
            avatar_small_url 用户小头像
            rank 排名
            level 用户等级
            segment 段位 starshine星耀 king王者 diamond钻石 platinum铂金 gold黄金 silver白银 bronze青铜
                (例:星耀1 starshine1;星耀王者2 king2)
            segment_text 段位文本 星耀1
            wealth_value string 榜单财富值
        }
        ...
    ]
    current_rank 当前排名
    changed_rank 变化的排名
    current_rank_text string 当前排名描述
}
```    