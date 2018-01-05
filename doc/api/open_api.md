# API接入综述

## 1. 引言

项目接口文档仅供公司内部项目使用

## 2. 调用API说明

### 2.1. 接口地址

调用环境|	服务域名
---|---
测试环境|http://ctest.yueyuewo.cn

### 2.2. 接口请求公共参数

参数名称|	类型|是否可空|说明
---|---|---|---
code|	string|	否|	产品渠道标识
dno|	string|	否|	设备号device_no缩写，客户端各自实现，必须支持合法校验
sid|	string|	否|	用户身份令牌，在登录或者更改密码后，会被更改掉
man|string|	否|	设备厂商 manufacturer缩写
mod|	string|	否|	设备型号 model缩写
an|	string|	否|	协议版本号
h|	string|	否|	签名验证字段
fr|	string|	否|	推广渠道标识
pf|	string|	否|	手机平台 platform 缩写，为ios 、 android 、jailbreak
pf_ver|	string|	否|	平台版本
verc|	int|	否|	软件版本号
ver|	string|	否|	软件版本名称，格式 x.y 格式，x和y必须都是数字
ts|	int|	否|	时间戳信息timestamp缩写, 必须为整型时间，秒
net|	string|	否|	net 网络制式 2g 3g 4g wifi
ckey|	string|	是|	客户端指纹，可为空
lat|	double|	是|	纬度latitude
lon|	double|	是|	经度longitude

### 2.3 响应公共参数

参数|名称|值类型|是否可空|备注
---|---|---|---|---
error_code|操作返回码|int|否| 0表示成功，非0为异常情况
error_reason|操作信息|string|	是|	返回失败原因
error_url|返回业务数据|string|是| 跳转地址
current_page|当前页|int|是| 分页查询时返回此参数
total_page|总页数|int|是| 分页查询时返回此参数
total_entries|总个数|int|是| 分页查询时返回此参数
