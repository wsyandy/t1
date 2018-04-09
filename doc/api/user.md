# 用户接口

### 1 用户注册协议

> http-get ```/m/product_channels/reg_agreement```

##### 1.1 请求参数说明
无

### 2 获取短信验证码接口

> http-post ```/api/users/send_auth```

##### 2.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| mobile |手机|string|否||
| sms_type |短信类型|string|否|登录login 注册register|
##### 2.2 回应参数说明
```
{
    error_code     0成功 -1失败
    error_reason  失败原因，默认为空  没账号登录返回"此手机号未注册" 有账号注册返回"此手机号已注册"
    sms_token 验证token
}
```

### 3 注册接口

> http-post ```/api/users/register```

##### 3.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|sms_token|短信验证|string|否| (伴随auth_code提供)|
|auth_code|验证码|string|否||
|password|密码|string|否||
|mobile|手机号码|string|否|||

##### 3.2 回应参数说明
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

### 4 登录接口

> http-post ```/api/users/login```

##### 4.1 手机号码登录
###### 4.1.1 参数
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|sms_token|短信验证|string|是| (忘记密码登录时提供)|
|auth_code|验证码|string|是|(忘记密码登录时提供)|
|mobile|手机号码|string|否||
|password|密码|string|否|||

###### 4.1.2 回应参数说明
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
### 5 退出登陆

> http-get ```/api/users/logout```

##### 5.1 请求参数说明
无

##### 5.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    sid : 设备的SID   123d......
}
```

### 6.1 用户基本信息

> http-get ``` /api/users/basic_info```

##### 6.1.1 请求参数说明

##### 6.1.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    id 用户id
    uid 用户uid 展示用户唯一标识
    sex	性别 0:女 1:男
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

### 6.2 用户详细信息

> http-get ``` /api/users/detail```

##### 6.2.1 请求参数说明

##### 6.2.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    id 用户id
    uid 用户uid 展示用户唯一标识
    sex	性别 0:女 1:男
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

### 6.3 他人用户详细信息

> http-get ``` /api/users/other_detail```

##### 6.3.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|user_id|用户id|int|是|查看他人资料传此参数|

##### 6.3.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    id 用户id
    uid 用户uid 展示用户唯一标识
    sex	性别 0:女 1:男
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

### 7 用户信息更新,完善资料

> http-post ```/api/users/update ```

##### 7.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|avatar_file|头像|file|||
|nickname|昵称|string|||
|sex|性别|int||0:女 1:男|
|province_name|省份名称|string|||
|city_name|城市名称|string|||
|monologue|个性签名|string||客户端需限制字数长度|
|age|年龄|int|||
|height|身高|int|||
|birthday|生日|string||||

##### 7.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    id 用户id
    sex	性别 0:女 1:男
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

### 8 上传头像

> http-post ```/api/users/update_avatar ```

##### 8.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|avatar_file|头像文件|file|否|||

##### 8.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
}
```

### 9 更新push_token

> http-post ```/api/users/push_token```

##### 9.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|push_token|||||
|push_from||||默认(getui),可以为getui,xinge|

##### 9.2 回应参数说明
```
{
    error_code: 0成功，非0失败
    error_reason  失败原因，默认为空
}
```

### 10 用户搜索接口

> http-post ```/api/users/search```

##### 10.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|user_id|用户id|int|是|hi老版,废弃|
|uid|用户uid|int|是|新接口|
|page|当前页|int|是||
|per_page|每页个数|int|是|||

##### 10.2 回应参数说明
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
### 11 附近的人

> http-get ```/api/users/nearby```

##### 11.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|page|页码|int|否||
|per_page|每页|int|否|||

##### 11.2 回应参数说明
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

### 12 设置扬声器

> http-post ```/api/users/set_speaker```

##### 12.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|speaker|扬声器|boole|否|false是静音

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
|microphone|麦克风|boole|否|false是静音

##### 13.2 回应参数说明
```
{
    error_code
    error_reason
}
```

### 14.用户使用条款

> http-get ```/m/product_channels/user_agreement```

##### 14.1 请求参数说明
```
公共参数
```

### 15. 隐私政策

> http-get ```/m/product_channels/privacy_agreement```

##### 15.1 请求参数说明
```
公共参数
```

### 16. 我的账户(h5)

> http-get ```/m/users/account```

##### 16.1 请求参数说明
```
公共参数
```

##### 16.2 返回说明
```
返回h5页面
```

### 17. 我的账户(只对iOS有效)

> http-get ```/api/users/account```

##### 17.1 请求参数说明
```
公共参数
```

##### 17.2 返回参数说明
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

### 18 第三方登录接口

> http-post ```/api/users/third_login```

##### 18.1 第三方登录(微信,QQ,新浪微博)
###### 18.1.1 参数
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|third_name|登录方式名称|string|否|微信:weixin,QQ:qq,新浪微博:sinaweibo|
|access_token|登录的token|string|否||
|openid|用户的唯一id|string|否||
|app_id|应用的appid|string|是|QQ登录需要此参数||

###### 18.1.2 回应参数说明
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

### 19. 我的礼物(h5)

> http-get ```/m/gift_orders```

##### 19.1 请求参数说明
```
公共参数
```

##### 19.2 返回说明
```
返回h5页面
```

### 20 扫码登录说明

    1. 用户打开登录后台。
    2. 用客户端扫描二维码
    3. 客户端访问二维码链接地址。会返回一个auth_url。
    4. 用户确认登录后再访问auth_url。error_reason 中会返回 确认成功 。到此客户端任务完成。
    5. 登录后台会自动跳转到对应的操作页面

### 21 我的曲库

> http-get ```/api/users/musics```

##### 21.1 参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|page|当前页码|int|否||
|per_page|每页个数|int|否|||

##### 21.2 回应参数说明
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
### 22 用户等级说明

> http-get ```/m/users/level_introduce``` 直接跳转

##### 22.1 请求参数说明
```
公共参数
```

### 23 判断是否签到
> http-get ```/api/users/is_sign_in```

##### 23.1 请求参数说明
```
公共参数
```

##### 23.2 回应参数说明
```
{
    error_code
    error_reason
    sign_in_status: 签到状态 1已签到，2未签到 
    tip: 恭喜您获得xxxx金币
    message: 七天以上连续签到可每天获得320金币
}
```

### 24 签到
> http-post ```/api/users/sign_in```

##### 24.1 请求参数说明
```
公共参数
```

##### 24.2 回应参数说明
```
{
    error_code: 0/-1
    error_reason: 失败原因，默认为空
    gold: 签到得到的金币
}
```


### 25 hi币贡献榜
>http-get ```/api/users/hi_coin_rank_list```

##### 25.1 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|list_type|榜单类型|string|否|day是日榜，week是周榜，total是总榜

##### 25.2 回应参数说明
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

### 26魅力榜
>http-get ```/api/users/charm_rank_list```

##### 26.1 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|list_type|榜单类型|string|否|day是日榜，week是周榜，total是总榜

##### 26.2 回应参数说明
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
    
### 27土豪榜
>http-get ```/api/users/wealth_rank_list```

##### 27.1 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|list_type|榜单类型|string|否|day是日榜，week是周榜，total是总榜

##### 27.2 回应参数说明
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