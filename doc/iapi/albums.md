# 相册

### 1 上传相册

> http-post ```/iapi/albums```

##### 1.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|image_file0~26|图片文件|file|否|上传的图片 支持多张上传,多张图片传参格式:image_file0,image_file1--image_file26最多传27张

##### 1.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
}
```

### 2 删除相册

> http-delete ```/iapi/albums```

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

### 3 获取相册

> http-get ```/iapi/albums```

##### 3.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|page|当前页|int|否||
|per_page|每页个数|int|是|默认为9

##### 3.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    albums:[
        {
            id 图片id
            user_id 用户id
            image_url 图片地址
            image_small_url 小图地址
            image_big_url 大图地址
        }
    ]
}
```