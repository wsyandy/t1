# 背包

## 1 礼物背包

> http-get ```/api/backpacks```


##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|type|背包target的类型|integer|可空|礼物默认为1

##### 返回参数说明
```
{
    "error_code": 0,
    "error_reason": "",
    "error_url": "",
    "now_at": 1525782941,
    "current_page": 1,
    "total_page": 1,
    "total_entries": 2,
    "backpacks": [
        {
            "id": 2,
            "number": 1,
            "image_url": "http:\/\/mt-development.img-cn-hangzhou.aliyuncs.com\/chance\/gifts\/image\/5acf07d875095.png",
            "svga_image_name": "",
            "render_type": "gif",
            "svga_image_url": "http:\/\/mt-development.img-cn-hangzhou.aliyuncs.com\/chance\/gifts\/image\/5acf07d875095.png@!small",
            "expire_day": 0,
            "show_rank": 0
        },
        {
            "id": 1,
            "number": 1,
            "image_url": "http:\/\/mt-development.img-cn-hangzhou.aliyuncs.com\/chance\/gifts\/image\/5acf07d875095.png",
            "svga_image_name": "",
            "render_type": "gif",
            "svga_image_url": "http:\/\/mt-development.img-cn-hangzhou.aliyuncs.com\/chance\/gifts\/image\/5acf07d875095.png@!small",
            "expire_day": 0,
            "show_rank": 0
        }
    ],
    "apple_stable_version": 19,
    "android_stable_version": 8
}
```