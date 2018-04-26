<?php
return [
    'admin' => '*',

    'editor' => ['dashboard' => '*', 'weixin_menu_templates' => '*', 'push_messages' => '*', 'weixin_template_messages' => '*'],

    'customer' => ['dashboard' => '*', 'users' => ['index', 'detail', 'basic', 'friend_list', 'followers', 'avatar'], 'id_card_auths' => '*',
        'withdraw_histories' => ['index']
    ],

    'tester' => ['dashboard' => '*', 'users' => '*', 'devices' => ['index', 'edit', 'update', 'white_list', 'add_white_list', 'delete_white_list'],
        'product_channels' => ['index', 'edit', 'update', 'push'], 'sms_histories' => '*', 'orders' => '*', 'gift_orders' => '*', 'voice_calls' => '*', 'albums' => ['index', 'show'],
        'rooms' => ['index', 'detail'], 'soft_versions' => '*', 'payment_channels' => ['index', 'product_channels', 'edit', 'update',],
        'account_histories' => '*'
    ],

    'operator' => ['dashboard' => '*', 'stats' => ['hours', 'days', 'partners'], 'wap_visits' => '*', 'word_visits' => '*', 'wap_visit_histories' => '*', 'word_visit_histories' => '*'],

    'operat_manager' => ['dashboard' => '*', 'stats' => ['hours', 'days', 'partners'], 'partners' => '*',
        'operators' => ['index', 'partners', 'update_partners'], 'channel_soft_versions' => '*',
        'wap_visits' => '*', 'word_visits' => '*', 'wap_visit_histories' => '*', 'word_visit_histories' => '*', 'gdt_configs' => '*',
        'partner_urls' => '*', 'export_histories' => '*'],

    'producter' => ['dashboard' => '*', 'product_channels' => '*', 'users' => '*', 'devices' => ['index', 'edit', 'update'],
        'orders' => '*', 'products' => '*', 'weixin_menu_templates' => '*', 'weixin_menus' => '*', 'weixin_sub_menus' => '*',
        'sms_channels' => '*', 'soft_versions' => '*', 'client_themes' => '*', 'push_messages' => '*', 'partners' => '*',
        'channel_soft_versions' => '*', 'gifts' => '*', 'emoticon_images' => '*', 'audios' => "*", 'room_themes' => '*',
        'musics' => '*', 'banners' => '*', 'complaints' => '*', 'sms_histories' => '*', 'rooms' => '*', 'broadcasts' => '*',
        'share_histories' => '*', 'audio_chapters' => '*', 'payment_channels' => '*', 'product_groups' => '*'
    ],

    'product_operator' => ['dashboard' => '*', 'users' => '*', 'devices' => ['index', 'edit', 'update'],
        'product_channels' => '*', 'products' => '*', 'push_messages' => '*', 'export_histories' => ['download'],
        'channel_soft_versions' => '*', 'word_visit_histories' => '*',
        'weixin_kefu_messages' => '*', 'weixin_template_messages' => '*', 'ge_tui_messages' => '*',
        'weixin_menu_templates' => '*', 'weixin_menus' => '*', 'weixin_sub_menus' => '*', 'id_card_auths' => '*',
        'unions' => ['day_rank_list', 'week_rank_list', 'index', 'edit', 'update', 'family'], 'gifts' => '*', 'emoticon_images' => '*', 'banners' => '*',
        'activities' => '*', 'rooms' => ['index', 'auto_hot', 'detail', 'add_user_agreement', 'delete_user_agreement', 'edit', 'update'],
        'room_themes' => '*', 'broadcasts' => '*', 'albums' => '*', 'account_histories' => ['index'], 'gold_histories' => ['index'],
        'gift_orders' => ['detail'], 'user_gifts' => ['index'], 'voice_calls' => ['index'], 'union_histories' => ['basic'], 'hi_coin_histories' => ['basic'],
        'withdraw_histories' => ['basic'], 'activity_histories' => ['basic'], 'withdraw_accounts' => ['index'], 'orders' => ['index'],
        'payments' => ['index'], 'audios' => '*', 'audio_chapters' => '*'
    ],
    'product_operator_assistant' => ['dashboard' => '*', 'users' => ['index', 'detail', 'edit', 'update'], 'rooms' => '*',
        'push_messages' => '*', 'weixin_kefu_messages' => '*', 'weixin_template_messages' => '*', 'banned_words' => ' *',
        'gifts' => '*', 'emoticon_images' => '*', 'banners' => '*', 'activities' => '*'
    ]
];