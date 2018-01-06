# 软件升级

### 1 软件升级

> http-get ```/api/soft_versions/upgrade```

##### 1.1 请求参数说明
```
公共参数
```

##### 1.2 回应参数说明
```
{
    error_code
    error_reason  失败原因，默认为空
    id 软件id
    file_url 安卓下载地址
    ios_down_url ios下载地址
    weixin_url 应用宝下载地址
    version_name 版本号
    version_code 版本code
    platform 平台
    feature 更新简介
    force_update 是否强制更新 0/1
    has_new_version 有无新版本 true/false 
}
```