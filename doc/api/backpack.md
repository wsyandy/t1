# 背包

## 1 礼物背包

> http-get ```/api/backpacks```


##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|page|页码|integer|否||
|per_page|每页个数|integer|可空|空表示数量20
|type|背包target的类型|integer|可空|礼物默认为1

##### 返回参数说明
```
{
    "error_code": 0,
    "error_reason": "",
    "error_url": "",
    "now_at": 1525776665,
    "current_page": 1,
    "total_page": 1,
    "total_entries": 2,
    "backpacks": [
        {
            "id": 2,
            "number": 1,
            "image_url": "http:\/\/mt-development.img-cn-hangzhou.aliyuncs.com\/chance\/gifts\/image\/5ad9ba5f53488.png"
        },
        {
            "id": 1,
            "number": 1,
            "image_url": "http:\/\/mt-development.img-cn-hangzhou.aliyuncs.com\/chance\/gifts\/image\/5ad5ff2c8ea9c.png"
        }
    ],
    "apple_stable_version": 19,
    "android_stable_version": 8
}
```