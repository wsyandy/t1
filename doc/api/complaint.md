# 举报

### 1 获取举报原因

> http-get ```/api/complaints/get_complaint_types```

##### 1.1 请求参数说明
```
公共参数
```

##### 1.2 回应参数说明
```
{
    error_code
    error_reason  失败原因，默认为空
    types: {
        1 : 色情,
        2 : 骚扰,
        3 : 不良信息 ,
        4 : 广告,
    }
}
```
### 2 举报

> http-post ```/api/complaints```

##### 2.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| user_id |用户id|int|是|举报用户时使用|
| room_id |房间id|int|是|举报房间时使用|
| type |举报类型|int|否|举报类型1,2,3,4|

##### 2.2 回应参数说明
```
{
    error_code
    error_reason  失败原因，默认为空
}
```