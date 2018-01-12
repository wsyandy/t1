# 1. App内部跳转协议

以下协议如果发现不符合规则的，请提出来讨论

## 地址协议说明
1. app://model/action?param1=value1&param2=value2 打开客户端功能 模块/功能,模块和功能的定义与api地址一致
2. url://m/model/action?param1=value1&param2=value2 客户端内打开wap页面相对地址（客户端需拼接域名）,打开该页面客户端需带上api接口调用的全局上行参数
3. 协议表管理: kv格式，原生页面协议表内嵌到app内部；weex页面协议表内嵌到主题模板；
4. 服务器端api协议是统一的，不针对客户端页面实现技术或版本做差异化；

## api协议
1. 首页：app://home
2. 返回上一页：app://back
3. 我的详情页面： app://users/detail?id=xxxx
4. 用户注册、登录: app://users/new
5. 用户退出：app://users/logout
6. 完善资料：app://users/update_info
7. 房间页面：app://rooms/detail?id=xxx
8. 其他用户详情页面：app://users/other_detail?user_id=xxx

## m协议
1. 注册登录协议 url://m/product_channels/reg_agreement

# 2. error_code 状态码协议

状态码|原因|说明
---|---|---
 0| 成功|根据具体业务处理
 -1| 失败|error_url不为空时需跳转error_url,为空时弹框提示错误原因error_reason
 -100| 需要登录|客户端跳转登录页面
 -1001| 需要弹框|根据具体业务处理,tip_content:弹框提示内容,tip_url:弹框跳转url