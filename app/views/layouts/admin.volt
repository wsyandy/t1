<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>管理后台</title>
    {{ js('/js/jquery/1.11.2/jquery.min.js','/js/vue/2.0.5/vue.min.js','/js/jquery.form/3.51.0/jquery.form.js',
    '/framework/bootstrap.select/1.11.2/js/bootstrap-select.min.js',
    '/framework/bootstrap/3.3.4/js/bootstrap.min.js',
    '/framework/bootstrap.datepicker/1.5.0/js/bootstrap-datepicker.min.js',
    '/framework/bootstrap.datepicker/1.4.0/js/bootstrap-datetimepicker.min.js',
    '/framework/bootstrap.datepicker/1.4.0/locales/bootstrap-datetimepicker.zh-CN.js',
    '/js/juicer/0.6.9/juicer-min.js','/js/echarts/2.2.7/echarts.js','/js/admin.js',
    '/framework/bootstrap.select/1.11.2/js/i18n/defaults-zh_CN.min.js') }}

    {{ css('/framework/bootstrap/3.3.4/css/bootstrap.min.css','/framework/bootstrap.datepicker/1.4.0/css/bootstrap-datetimepicker.min.css',
    '/framework/bootstrap.datepicker/1.5.0/css/bootstrap-datepicker.min.css','/css/admin.css', '/framework/bootstrap.select/1.11.2/css/bootstrap-select.min.css') }}

</head>
<body>

<nav class="navbar navbar-default navbar-static-top {% if is_development %}dev_navbar{% endif %}" role="navigation"
     style="padding-left: 10px;padding-right: 10px;">

    <ul class="nav navbar-nav">
        {% if isAllowed('product_channels','index') %}
            <li>
                <a href="/admin/product_channels">产品渠道</a>
            </li>
        {% endif %}

        {% if isAllowed('users','index') or isAllowed('devices','index') %}
            <li>
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">用户<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    {% if isAllowed('devices','index') %}
                        <li>
                            <a href="/admin/devices">激活列表</a>
                        </li>
                    {% endif %}
                    {% if isAllowed('users','index') %}
                        <li>
                            <a href="/admin/users">用户列表</a>
                        </li>
                    {% endif %}
                    {% if isAllowed('sms_histories','index') %}
                        <li><a href="/admin/sms_histories">短信验证列表</a></li>
                    {% endif %}
                    {% if isAllowed('complaints','index') %}
                        <li><a href="/admin/complaints">举报列表</a></li>
                    {% endif %}
                    {% if isAllowed('rooms','index') %}
                        <li><a href="/admin/rooms">房间列表</a></li>
                    {% endif %}
                </ul>
            </li>
        {% endif %}

        {% if isAllowed('orders','index') or isAllowed('gift_orders','index') %}
        <li>
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">订单<b class="caret"></b></a>
            <ul class="dropdown-menu">
                {% if isAllowed('orders', 'index') %}
                    <li><a href="/admin/orders">订单列表</a></li>
                {% endif %}
                {% if isAllowed('gift_orders','index') %}
                    <li><a href="/admin/gift_orders">礼物订单列表</a></li>
                {% endif %}
            </ul>
        </li>
        {% endif %}

        <!--微信管理-->
        {% if isAllowed('weixin_menu_templates','index') or isAllowed('push_messages','index') or isAllowed('weixin_template_messages','index') %}
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">微信管理<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    {% if isAllowed('weixin_menu_templates','index') %}
                        <li><a href="/admin/weixin_menu_templates">微信菜单模板</a></li>
                    {% endif %}
                    {% if isAllowed('push_messages','index') %}
                        <li><a href="/admin/push_messages">离线消息配置</a></li>
                    {% endif %}
                    {% if isAllowed('weixin_kefu_messages','index') %}
                        <li><a href="/admin/weixin_kefu_messages">发送客服消息</a></li>
                    {% endif %}
                    {% if isAllowed('weixin_template_messages','index') %}
                        <li><a href="/admin/weixin_template_messages">发送模板消息</a></li>
                    {% endif %}
                </ul>
            </li>
        {% endif %}

        <!-- 统计 -->
        {% if isAllowed('stats','hours') or isAllowed('stats','days') %}

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    统计<b class="caret"></b>
                </a>
                <ul class="dropdown-menu">
                    {% if isAllowed('stats','hours') %}
                        <li><a href="/admin/stats/hours">小时统计</a></li>
                    {% endif %}
                    {% if isAllowed('stats','days') %}
                        <li><a href="/admin/stats/days">按天统计</a></li>
                    {% endif %}

                    {% if isAllowed('sms_histories','push_stat') %}
                        <li class="dropdown-submenu">
                            <a href="javascript:;" tabindex="-1">短信下发统计</a>
                            <ul class="dropdown-menu">
                                {% if isAllowed('sms_histories','login_stat') %}
                                    <li><a href="/admin/sms_histories/login_stat">登录按天统计</a></li>
                                {% endif %}
                                {% if isAllowed('sms_histories','login_hout_stat') %}
                                    <li><a href="/admin/sms_histories/login_hour_stat">登录小时统计</a></li>
                                {% endif %}
                            </ul>
                        </li>
                    {% endif %}

                    {% if isAllowed('wap_visits', 'index') %}
                        <li><a href="/admin/wap_visits">SEM落地页统计</a></li>
                    {% endif %}
                    {% if isAllowed('word_visits', 'index') %}
                        <li><a href="/admin/word_visits">SEM关键词统计</a></li>
                    {% endif %}
                </ul>
            </li>
        {% endif %}

        <!-- 系统 -->
        {% if isAllowed('operators','index') or isAllowed('partners','index') or isAllowed('sms_channels','index')
        or isAllowed('provinces','index') or isAllowed('protocol_urls','index') or isAllowed('banners','index')
        or isAllowed('soft_versions','index') or isAllowed('sms_channels','index') or isAllowed('partner_urls','index')
        or isAllowed('marketing_configs','index') or isAllowed('cooperate', 'index') %}

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    系统<b class="caret"></b>
                </a>
                <ul class="dropdown-menu">
                    {% if isAllowed('operators','index') or isAllowed('operators','operator_login_histories') %}
                        <li class="dropdown-submenu">
                            <a href="javascript:;" tabindex="-1">操作员管理</a>
                            <ul class="dropdown-menu">
                                {% if isAllowed('operators','index') %}
                                    <li><a href="/admin/operators">操作员列表</a></li>
                                {% endif %}
                                {% if isAllowed('operator_login_histories','operator_login_histories') %}
                                    <li><a href="/admin/operator_login_histories">登录记录</a></li>
                                {% endif %}
                                {% if isAllowed('operating_records','index') %}
                                    <li><a href="/admin/operating_records">操作记录</a></li>
                                {% endif %}
                            </ul>
                        </li>
                    {% endif %}
                    {% if isAllowed('partners','index') %}
                        <li><a href="/admin/partners">推广渠道</a></li>
                    {% endif %}
                    {% if isAllowed('channel_soft_versions','index') %}
                        <li><a href="/admin/channel_soft_versions">推广渠道包</a></li>
                    {% endif %}
                    {% if isAllowed('partner_urls', 'index') %}
                        <li><a href="/admin/partner_urls">推广链接生成</a></li>
                    {% endif %}
                    {% if isAllowed('gdt_configs', 'index') %}
                        <li><a href="/admin/gdt_configs">广点通账户</a></li>
                    {% endif %}
                    {% if isAllowed('marketing_configs', 'index') %}
                        <li><a href="/admin/marketing_configs">腾讯marketing配置</a></li>
                    {% endif %}
                    {% if isAllowed('sms_channels','index') %}
                        <li><a href="/admin/sms_channels">短信渠道</a></li>
                    {% endif %}
                    {% if isAllowed('provinces','index') %}
                        <li><a href="/admin/provinces">省市管理</a></li>
                    {% endif %}
                    {% if isAllowed('soft_versions','index') %}
                        <li><a href="/admin/soft_versions">软件升级管理</a></li>
                    {% endif %}
                    {% if isAllowed('export_histories','index') %}
                        <li><a href="/admin/export_histories">导出记录</a></li>
                    {% endif %}
                    {% if isAllowed('gifts', 'index') %}
                        <li><a href="/admin/gifts">礼物配置</a></li>
                    {% endif %}
                    {% if isAllowed('payment_channels', 'index') %}
                        <li><a href="/admin/payment_channels">支付配置</a></li>
                    {% endif %}
                </ul>
            </li>
        {% endif %}


        <!--监控管理-->
        {% if isAllowed('monitor','redis') %}
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">监控<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    {% if isAllowed('monitor','redis') %}
                        <li><a href="/admin/monitor/redis" target="_blank">异步监控</a></li>
                    {% endif %}
                </ul>
            </li>
        {% endif %}

    </ul>

    <ul class="nav navbar-nav navbar-right">
        <li><a>{{ current_operator.username }}</a></li>
        <li><a href="/admin/home/logout">注销</a></li>
    </ul>

</nav>

<div style="padding:0 15px;">
    {{ content() }}
</div>

</body>
</html>