# 相册

### 1 上传相册

> http-post ```/api/albums```

##### 1.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|image_file|图片文件|file|否|上传的图片

##### 1.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    album{
        id,
        user_id,
        image_url 原图
        image_small_url 小图
        image_big_url 小图
    }
}
```

### 2 删除相册

> http-delete ```/api/albums```

##### 2.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| id |相册图片id|int|否|||

##### 2.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
}
```