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
| mobile |手机|string|否|||

##### 2.2 回应参数说明
```
{
    error_code
    error_reason  失败原因，默认为空
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
    microphone 麦克风状态 false/true 默认为true
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
    microphone 麦克风状态 false/true 默认为true
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
    new_friend_num 新好友人数
    interests 兴趣爱好
    height 身高
    im_password 即时通信登录密码
    age 年龄
    birthday 生日
    constellation 星座
    user_role 当前用户角色，无角色，房主，主播，旁听
    mobile 手机号
    speaker 扬声器状态 false/true 默认为true
    microphone 麦克风状态 false/true 默认为true
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
    lock 房间是否加锁 true/false
    monologue 个人签名
    followed_num 粉丝人数
    follow_num 关注人数,
    friend_num 好友人数
    new_friend_num 新好友人数
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
    microphone 麦克风状态 false/true 默认为true
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
    new_friend_num 新好友人数
    interests 兴趣爱好
    height 身高
    im_password 即时通信登录密码
    age 年龄
    birthday 生日
    constellation 星座
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
|user_id|用户id|int|是||
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


