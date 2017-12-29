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
		    error_code
		    error_reason
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