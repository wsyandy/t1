# 房间麦位

### 1 上麦或抱用户上麦

> http-post ```/api/room_seats/up```

##### 1.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否|
|user_id|用户id|int|是|自己上麦可为空，抱用户上麦不为空

##### 1.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```


### 2 下麦或设为旁听 

> http-post ```/api/room_seats/down```

##### 2.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|麦位id|int|否|
|user_id|用户id|int|是|自己下麦可为空，设为旁听不为空

##### 2.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```