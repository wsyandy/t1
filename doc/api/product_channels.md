# 产品渠道

## 1. 产品渠道信息

> http-get ```/api/product_channels/detail```
 
##### 1.1 请求参数说明

```
公共参数
```

##### 1.2 返回参数说明
```
{
    error: 0/-1 
    error_reason: ''
    official_website : string 官网地址
}
```

## 2. 启动配置

> http-get ```/api/product_channels/boot_config```
 
##### 2.1 请求参数说明

```
公共参数
```

##### 2.2 返回参数说明
```
{
    error: 0/-1 
    error_reason: ''
    menu_config : [ //菜单配置
        {
            show boolean 是否展示 true展示 false不展示
            title string 名称
            url string 跳转地址
            icon string 图片地址
        }
    ]
}
```