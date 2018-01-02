# 房间频道

### 1 创建房间

> http-post ```/api/rooms/create```

##### 1.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|name|房间名称|string|否|

##### 1.2 回应参数说明
```
{
		    error_code
		    error_reason
            room:{
                id: int 房间id,
                name: string 房间名称
            } 
}
```

### 2 更新房间信息

> http-post ```/api/rooms/update```

##### 2.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|
|name|房间名称|string|是|
|topic|房间话题|string|是|

##### 2.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```

### 3 进入房间

> http-post ```/api/rooms/enter```

##### 3.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|

##### 3.2 回应参数说明
```
{
		    error_code,
		    error_reason,
		    name 房间名称，
		    topic 话题
		    user_num 在线人数
		    speaker 扬声器 false/true
		    microphone 麦克风 false/true
}
```

### 4 退出房间

> http-post ```/api/rooms/exit```

##### 4.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|

##### 4.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```

### 5 房间加锁

> http-post ```/api/rooms/lock```

##### 5.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|

##### 5.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```

### 6 房间解锁

> http-post ```/api/rooms/unlock```

##### 6.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|

##### 6.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```

### 7 打开公屏

> http-post ```/api/rooms/open_chat```

##### 7.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|

##### 7.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```

### 8 关闭公屏

> http-post ```/api/rooms/close_chat```

##### 8.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|

##### 8.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```

### 9 房间用户列表

> http-get ```/api/rooms/users```

##### 9.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|房间id|int|否|
|page|页码|int|否|
|per_page|每页个数|int|否|默认8个

##### 9.2 回应参数说明
```
{
		    error_code,
		    error_reason,
		    users:[
		        {
		           
		        },
		        ...
		    ]
}
```


### 10 设置扬声器

> http-post ```/api/users/set_speaker```

##### 10.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|speaker|扬声器|boole|否|

##### 10.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```


### 11 设置麦克风

> http-post ```/api/users/set_microphone```

##### 11.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|microphone|麦克风|boole|否|

##### 11.2 回应参数说明
```
{
		    error_code
		    error_reason
}
```
