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
    '/js/juicer/0.6.9/juicer-min.js','/js/echarts/3.7.2/echarts.js'
    ,'/js/echarts/3.7.2/chalk.js','/js/echarts/3.7.2/china.js','/js/admin.js',
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

        {% if isAllowed('users','index') or isAllowed('devices','index') or isAllowed('devices','white_list') or isAllowed('users','avatar')
        or isAllowed('third_auths','index') or  isAllowed('sms_histories','index') or isAllowed('complaints','index') or
        isAllowed('rooms','index') or isAllowed('share_histories','index') or isAllowed('access_tokens','index') %}
            <li>
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">用户<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    {% if is_development and false %}
                        <li>
                            <a href="/admin/users/avatar">头像审核</a>
                        </li>
                        <li>
                            <a href="/admin/users/select_avatar">选择头像</a>
                        </li>
                    {% endif %}

                    {% if isAllowed('devices','index') %}
                        <li>
                            <a href="/admin/devices">激活列表</a>
                        </li>
                    {% endif %}
                    {% if isAllowed('devices','white_list') %}
                        <li>
                            <a href="/admin/devices/white_list">设备号白名单</a>
                        </li>
                    {% endif %}
                    {% if isAllowed('users','index') %}
                        <li>
                            <a href="/admin/users">用户列表</a>
                        </li>
                    {% endif %}
                    {% if isAllowed('third_auths','index') %}
                        <li>
                            <a href="/admin/third_auths">第三方登录</a>
                        </li>
                    {% endif %}
                    {% if isAllowed('sms_histories','index') %}
                        <li><a href="/admin/sms_histories">短信验证列表</a></li>
                    {% endif %}
                    {% if isAllowed('complaints','index') %}
                        <li><a href="/admin/complaints">举报列表</a></li>
                    {% endif %}
                    {% if isAllowed('share_histories','index') %}
                        <li><a href="/admin/share_histories">分享记录</a></li>
                    {% endif %}
                    {% if isAllowed('access_tokens','index') and is_development %}
                        <li><a href="/admin/access_tokens">授权登录记录</a></li>
                    {% endif %}
                    {% if isAllowed('users','company_user') %}
                        <li><a href="/admin/users/company_user">公司内部用户</a></li>
                    {% endif %}
                    {% if isAllowed('users','reserved') %}
                        <li><a href="/admin/users/reserved">预留靓号</a></li>
                    {% endif %}
                    {% if isAllowed('game_histories','index') %}
                        <li><a href="/admin/game_histories">游戏记录</a></li>
                    {% endif %}
                    {% if isAllowed('rooms','game_white_list') %}
                        <li><a href="/admin/rooms/game_white_list">游戏白名单</a></li>
                    {% endif %}
                </ul>
            </li>
        {% endif %}

        {% if isAllowed('rooms','index') or isAllowed('rooms','auto_hot') or isAllowed('broadcasts','index') or isAllowed('room_categories','index') %}
            <li>
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">房间<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    {% if isAllowed('rooms','index') %}
                        <li><a href="/admin/rooms">房间列表</a></li>
                    {% endif %}
                    {% if isAllowed('rooms','auto_hot') %}
                        <li><a href="/admin/rooms/auto_hot">热门房间</a></li>
                    {% endif %}
                    {% if isAllowed('rooms','index') %}
                        <li><a href="/admin/rooms?hot=1">固定热门房间</a></li>
                    {% endif %}
                    {% if isAllowed('broadcasts','index') %}
                        <li><a href="/admin/broadcasts">电台房间列表</a></li>
                    {% endif %}
                    {% if isAllowed('room_categories','index') %}
                        <li><a href="/admin/room_categories/index">房间分类</a></li>
                    {% endif %}
                    {% if isAllowed('room_tags','index') %}
                        <li><a href="/admin/room_tags/index">房间类型</a></li>
                    {% endif %}
                </ul>
            </li>
        {% endif %}

        {% if isAllowed('unions','index') or isAllowed('hot_room_histories','index') or isAllowed('id_card_auths','index') %}
            <li>
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">公会和家族<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    {% if isAllowed('unions', 'index') %}
                        <li><a href="/admin/unions?auth_status=1">公会</a></li>
                    {% endif %}
                    {% if isAllowed('unions', 'index') %}
                        <li><a href="/admin/unions?auth_status=3">待审核的公会</a></li>
                    {% endif %}
                    {% if isAllowed('unions', 'family') %}
                        <li><a href="/admin/unions/family">家族</a></li>
                    {% endif %}
                    {% if isAllowed("hot_room_histories",'index') %}
                        <li><a href="/admin/hot_room_histories">上热门申请</a></li>
                    {% endif %}
                    {% if isAllowed("id_card_auths",'index') %}
                        <li><a href="/admin/id_card_auths">主播认证</a></li>
                    {% endif %}
                </ul>
            </li>
        {% endif %}


        {% if isAllowed('orders','index') or isAllowed('gift_orders','index') or isAllowed('withdraw_histories','index') %}
            <li>
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">订单<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    {% if isAllowed('orders', 'index') %}
                        <li><a href="/admin/orders">订单列表</a></li>
                    {% endif %}
                    {% if isAllowed('hi_coin_histories', 'orders') %}
                        <li><a href="/admin/hi_coin_histories/orders">Hi币订单列表</a></li>
                    {% endif %}
                    {% if isAllowed('payments', 'index') %}
                        <li><a href="/admin/payments">支付流水</a></li>
                    {% endif %}
                    {% if isAllowed('gift_orders','index') %}
                        <li><a href="/admin/gift_orders">礼物订单列表</a></li>
                    {% endif %}
                    {% if isAllowed('withdraw_histories','index') %}
                        <li><a href="/admin/withdraw_histories?withdraw_history[status_eq]=0">提现列表</a></li>
                    {% endif %}
                </ul>
            </li>
        {% endif %}

        <!--消息管理-->
        {% if isAllowed('weixin_menu_templates','index') or isAllowed('push_messages','index') or isAllowed('weixin_template_messages','index') %}
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">消息管理<b class="caret"></b></a>
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
                    {% if isAllowed('banned_words','index') %}
                        <li><a href="/admin/banned_words">违禁词</a></li>
                    {% endif %}
                </ul>
            </li>
        {% endif %}

        <!-- 统计 -->
        {% if isAllowed('stats','hours') or isAllowed('stats','days') or isAllowed('active_users','day_rank_list') or
        isAllowed('active_users','month_rank_list') or isAllowed('sms_histories','push_stat') or isAllowed('stats','partners')
        or isAllowed('wap_visits','index') or isAllowed('word_visits','days') or isAllowed('stats', 'stat_room_time') or
        isAllowed('users','day_rank_list') or  isAllowed('users','week_rank_list') or  isAllowed('users','total_rank_list') or
        isAllowed('unions','day_rank_list') or isAllowed('unions','week_rank_list') or isAllowed('activities', 'stat') or
        isAllowed('gift_stats', 'days') %}

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
                    {% if isAllowed('payments','day_stat') %}
                        <li><a href="/admin/payments/day_stat">支付方式统计</a></li>
                    {% endif %}
                    {% if isAllowed('active_users','day_rank_list') or isAllowed('active_users','month_rank_list') %}
                        <li class="dropdown-submenu">
                            <a href="javascript:;" tabindex="-1">在线用户统计</a>
                            <ul class="dropdown-menu">
                                {% if isAllowed('active_users','day_rank_list') %}
                                    <li><a href="/admin/active_users/day_rank_list">按天统计</a></li>
                                {% endif %}
                            </ul>
                        </li>
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

                    {% if isAllowed('stats', 'partners') %}
                        <li><a href="/admin/stats/partners">渠道按天统计</a></li>
                    {% endif %}
                    {% if isAllowed('wap_visits', 'index') %}
                        <li><a href="/admin/wap_visits">SEM落地页统计</a></li>
                    {% endif %}
                    {% if isAllowed('word_visits', 'index') %}
                        <li><a href="/admin/word_visits">SEM关键词统计</a></li>
                    {% endif %}
                    {% if isAllowed('stats', 'stat_room_time') %}
                        <li><a href="/admin/stats/stat_room_time">用户主播时长统计</a></li>
                    {% endif %}
                    {% if isAllowed('room_stats','total_stat') or  isAllowed('room_stats','day_stat') %}
                        <li class="dropdown-submenu">
                            <a href="javascript:;" tabindex="-1">房间统计</a>
                            <ul class="dropdown-menu">
                                {% if isAllowed('room_stats','total_stat') %}
                                    <li><a href="/admin/room_stats/total_stat">房间收益总的统计</a></li>
                                {% endif %}
                                {% if isAllowed('room_stats','day_stat') %}
                                    <li><a href="/admin/room_stats/day_stat">房间收益按天统计</a></li>
                                {% endif %}
                            </ul>
                        </li>
                    {% endif %}

                    {% if isAllowed('users','day_rank_list') or  isAllowed('users','week_rank_list') or  isAllowed('users','total_rank_list') %}
                        <li class="dropdown-submenu">
                            <a href="javascript:;" tabindex="-1">排行榜统计</a>
                            <ul class="dropdown-menu">
                                {% if isAllowed('users','day_rank_list') %}
                                    <li><a href="/admin/users/day_rank_list">日榜统计</a></li>
                                {% endif %}
                                {% if isAllowed('users','week_rank_list') %}
                                    <li><a href="/admin/users/week_rank_list">周榜统计</a></li>
                                {% endif %}
                                {% if isAllowed('users','total_rank_list') %}
                                    <li><a href="/admin/users/total_rank_list">总榜统计</a></li>
                                {% endif %}
                            </ul>
                        </li>
                    {% endif %}
                    {% if isAllowed('unions','day_rank_list') or isAllowed('unions','week_rank_list') or isAllowed('unions','total_rank_list') %}
                        <li class="dropdown-submenu">
                            <a href="javascript:;" tabindex="-1">家族排行榜统计</a>
                            <ul class="dropdown-menu">
                                {% if isAllowed('unions','day_rank_list') %}
                                    <li><a href="/admin/unions/day_rank_list">日榜统计</a></li>
                                {% endif %}
                                {% if isAllowed('unions','week_rank_list') %}
                                    <li><a href="/admin/unions/week_rank_list">周榜统计</a></li>
                                {% endif %}
                                {% if isAllowed('unions','total_rank_list') %}
                                    <li><a href="/admin/unions/total_rank_list">总榜统计</a></li>
                                {% endif %}
                            </ul>
                        </li>
                    {% endif %}

                    {% if isAllowed('activities', 'stat') %}
                        <li><a href="/admin/activities/stat">活动统计</a></li>
                    {% endif %}

                    {% if isAllowed('gift_stats', 'days') %}
                        <li><a href="/admin/gift_stats/days">礼物统计</a></li>
                    {% endif %}

                    {% if isAllowed('users', 'wakeup_stat') %}
                        <li><a href="/admin/users/wakeup_stat">唤醒离线24小时用户统计</a></li>
                    {% endif %}
                    {% if isAllowed('partner_datas','index') %}
                        <li><a href="/admin/partner_datas/index">推广激活统计</a></li>
                    {% endif %}
                </ul>
            </li>
        {% endif %}

        <!-- 系统 -->
        {% if isAllowed('operators','index') or isAllowed('partners','index') or isAllowed('sms_channels','index')
        or isAllowed('provinces','index') or isAllowed('protocol_urls','index') or isAllowed('banners','index')
        or isAllowed('soft_versions','index') or isAllowed('sms_channels','index') or isAllowed('partner_urls','index')
        or isAllowed('marketing_configs','index') or isAllowed('cooperate', 'index') or isAllowed('payment_channels','index') %}
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
                    {% if isAllowed('sina_ad_configs', 'index') %}
                        <li><a href="/admin/sina_ad_configs">新浪扶翼配置</a></li>
                    {% endif %}
                    {% if isAllowed('soft_versions','index') %}
                        <li><a href="/admin/soft_versions">软件升级管理</a></li>
                    {% endif %}
                    {% if isAllowed('sms_channels','index') %}
                        <li><a href="/admin/sms_channels">短信渠道</a></li>
                    {% endif %}
                    {% if isAllowed('payment_channels', 'index') %}
                        <li><a href="/admin/payment_channels">支付配置</a></li>
                    {% endif %}
                    {% if isAllowed('provinces','index') %}
                        <li><a href="/admin/provinces">省市管理</a></li>
                    {% endif %}
                    {% if isAllowed('countries','index') %}
                        <li><a href="/admin/countries">国家管理</a></li>
                    {% endif %}
                    {% if isAllowed('export_histories','index') %}
                        <li><a href="/admin/export_histories">导出记录</a></li>
                    {% endif %}
                    {% if isAllowed('partner_accounts','index') %}
                        <li><a href="/admin/partner_accounts">合作方账号管理</a></li>
                    {% endif %}
                </ul>
            </li>
        {% endif %}

        {% if isAllowed('gifts','index') or isAllowed('emoticon_images','index') or  isAllowed('audios','index')
        or isAllowed('room_themes','index') or isAllowed('musics','index') or isAllowed('account_banks','index')
        or isAllowed('activities','index') %}
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">资源配置<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    {% if isAllowed('i_gifts', 'index') %}
                        <li><a href="/admin/i_gifts">礼物配置(国际版)</a></li>
                    {% endif %}
                    {% if isAllowed('gifts', 'index') %}
                        <li><a href="/admin/gifts">礼物配置</a></li>
                    {% endif %}
                    {% if isAllowed('emoticon_images','index') %}
                        <li><a href="/admin/emoticon_images">表情配置</a></li>
                    {% endif %}
                    {% if isAllowed('audios','index') %}
                        <li><a href="/admin/audios">音频配置</a></li>
                    {% endif %}
                    {% if isAllowed('room_themes','index') %}
                        <li><a href="/admin/room_themes">主题配置</a></li>
                    {% endif %}
                    {% if isAllowed('musics','index') %}
                        <li><a href="/admin/musics">音乐配置</a></li>
                    {% endif %}
                    {% if isAllowed('banners','index') %}
                        <li><a href="/admin/banners">banner配置</a></li>
                    {% endif %}
                    {% if isAllowed('account_banks','index') %}
                        <li><a href="/admin/account_banks">账户银行配置</a></li>
                    {% endif %}
                    {% if isAllowed('activities','index') %}
                        <li><a href="/admin/activities">活动配置</a></li>
                    {% endif %}
                    {% if isAllowed('games','index') %}
                        <li><a href="/admin/games">游戏配置</a></li>
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