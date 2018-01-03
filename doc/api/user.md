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
    sid 身份信息 必须先更新本地SID
    error_url 跳转地址
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
    sid 身份信息 必须先更新本地SID
    error_url 跳转地址
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

### 6 用户详细信息

> http-get ``` /api/users/detail```

##### 6.1 请求参数说明

##### 6.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    sex	性别 0:女 1:男
    province_id 现居省份
    province_name 省名
    city_id 现居城市
    city_name 城市
    avatar_url 用户头像
    avatar_small_url 用户小头像
    nickname 昵称
    mobile  string 手机号
    room_id 房间id
    monologue 个人签名
    followed_num 粉丝人数
    follow_num 关注人数,
    friend_num 好友人数
    interests 兴趣爱好
    height 身高
    albums:[
        {
            id,
            image_url 原图
            image_small_url 小图
            image_big_url 小图
        }
        ...
    ],
    user_gifts:[
        {
        gift_id,
        name,
        amount int 礼物金额
        pay_type string 支付类型 gold:金币 diamond:钻石
        image_url 原图
        image_small_url 小图
        image_big_url 小图
        num 礼物个数
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
|uid|用户id|int|是|查看他人资料传此参数|

##### 6.3.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    uid 用户id
    sex	性别 0:女 1:男
    province_id 现居省份
    province_name 省名
    city_id 现居城市
    city_name 城市
    avatar_url 用户头像
    avatar_small_url 用户小头像
    nickname 昵称
    mobile  string 手机号
    room_id 房间id
    monologue 个人签名
    followed_num 粉丝人数
    follow_num 关注人数,
    friend_num 好友人数
    is_friend true/false,好友
    interests 兴趣爱好
    height 身高
    albums:[
        {
            id,
            image_url 原图
            image_small_url 小图
            image_big_url 小图
        }
        ...
    ],
    user_gifts:[
        {
        gift_id,
        name,
        amount int 礼物金额
        pay_type string 支付类型 gold:金币 diamond:钻石
        image_url 原图
        image_small_url 小图
        image_big_url 小图
        num 礼物个数
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
|nickname|昵称|string|||
|sex|性别|int||0:女 1:男|
|province_name|省份名称|string|||
|city_name|城市名称|string||||

##### 7.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
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






