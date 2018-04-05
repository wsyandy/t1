# 设备

### 1 激活设备接口

> http-post ```/iapi/devices/active```

##### 1.1 请求参数说明
|参数|类型|是否可空|备注
|---|---|---|---
|dno|string|	否|	设备号device_no缩写，客户端各自实现，必须支持合法校验
|ua|string|否|User Agent
|ei|string|否|安卓imei，base64加密，若使用imei，可为空
|imei|string|否|安卓imei明文，若使用ei，可为空
|if|string|否|苹果idfa，base64加密，若使用idfa，可为空
|idfa|string|否|苹果idfa明文，若使用if，可为空
|imsi|string|是|手机imsi
|gc|string|是|手机网关mac, gateway_mac

##### 1.2 回应参数说明
```
{
    error_code
    error_reason  失败原因，默认为空
    sid : 设备的SID    
}
```