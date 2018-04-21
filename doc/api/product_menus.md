# 产品菜单

## 1. 菜单列表

> http-get ```/api/product_menus```
 
##### 1.1 请求参数说明
```
   公共参数
```

##### 1.2 返回参数说明
```
{
    error: 0/-1 
    error_reason: ''
    product_menus:[
        {
            name string 热门
            type string 推荐 recommend  附近 nearby 
        }
    ]
}
```