# 关注接口

### 1 关注列表接口

> http-get ```/api/followers```

##### 1.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| page |第几页|int|否|传递的页数|
| per_page |每页的条数|int|是|每页的数据条数，默认10|

##### 1.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    current_page  当前页数
    total_page   总页数
    total_entries   总记录数
    users  用户信息数组
}
```

### 2 被关注列表接口

> http-get ```/api/followers/list```

##### 2.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| page |第几页|int|否|传递的页数|
| per_page |每页的条数|int|是|每页的数据条数，默认10|

##### 2.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    current_page  当前页数
    total_page   总页数
    total_entries   总记录数
    users  用户信息数组
}
```

### 3 关注接口

> http-post ```/api/followers```

##### 3.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| uid |uid|string|否|||

##### 3.2 回应参数说明
```
{
	error_code  0 成功，非0失败
}
```

### 4 取消关注接口

> http-delete ```/api/followers```

##### 4.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| uid |uid|string|否|||

##### 4.2 回应参数说明
```
{
	error_code  0 成功，非0失败
}
```