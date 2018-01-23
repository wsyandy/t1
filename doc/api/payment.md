# 支付上报

## 1. iOS审核版本苹果支付支付上报

> http-post ```/api/payments/apple_result```
 
##### 1.1 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|data|支付结果|string|否||
|product_id|产品ID|integer|否

##### 1.2 返回参数说明
```
{
    error: 0/-1 
    error_reason: ''
}
```