# 家族

## 1. 家族

> http-get ```/m/unions```  直接跳转
 
##### 1.1 请求参数说明

```
公共参数
```

## 2.搜索家族

> http-get ```/api/unions/search```

##### 2.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|uid|家族uid|int|是||
|name|昵称|string|是|||

##### 2.2 回应参数说明
```
{
    error_code: 0成功，非0失败
    error_reason  失败原因，默认为空
    unions:[
        {
            id 用户id
            name 昵称
            fame_value 声望值
            user_num 成员数量
            avatar_url 用户头像
            avatar_small_url 用户小头像
            url 家族详情链接 url://m/unions/my_union
        }
        ...
    ]
}
```

## 3.推荐家族

> http-get ```/api/unions/recommend```

##### 3.1 请求参数说明

```
公共参数
```

##### 3.2 回应参数说明
```
{
    error_code: 0成功，非0失败
    error_reason  失败原因，默认为空
    unions:[
          {
                     id 用户id
                     name 昵称
                     fame_value 声望值
                     user_num 成员数量
                     avatar_url 用户头像
                     avatar_small_url 用户小头像
                     url 家族详情链接 url://m/unions/my_union
          }
          ...
    ]
}
```