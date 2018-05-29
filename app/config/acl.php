<?php
return [
    'admin' => '*',

    'editor' => ['dashboard' => '*', 'weixin_menu_templates' => '*', 'push_messages' => '*', 'weixin_template_messages' => '*'],

    'customer' => ['dashboard' => '*', 'users' => ['index', 'detail', 'basic', 'friend_list', 'followers', 'avatar', 'send_message'], 'id_card_auths' => '*',
        'withdraw_histories' => ['index'], 'rooms' => ['index', 'forbidden_to_hot'], 'complaints' => ['index']
    ],

    'tester' => ['dashboard' => '*', 'users' => '*', 'devices' => ['index', 'edit', 'update', 'white_list', 'add_white_list', 'delete_white_list'],
        'product_channels' => ['index', 'edit', 'update', 'push'], 'sms_histories' => '*', 'orders' => '*', 'gift_orders' => '*', 'voice_calls' => '*', 'albums' => ['index', 'show'],
        'rooms' => ['index', 'detail', 'game_white_list', 'add_game_white_list'], 'soft_versions' => '*', 'payment_channels' => ['index', 'product_channels', 'edit', 'update',],
        'account_histories' => '*'
    ],

    'operator' => ['dashboard' => '*', 'stats' => ['hours', 'days', 'partners'], 'wap_visits' => '*', 'word_visits' => '*', 'wap_visit_histories' => '*', 'word_visit_histories' => '*',
        'partner_datas' => '*'
    ],

    'operat_manager' => ['dashboard' => '*', 'stats' => ['hours', 'days', 'partners'], 'partners' => '*',
        'operators' => ['index', 'partners', 'update_partners'], 'channel_soft_versions' => '*',
        'wap_visits' => '*', 'word_visits' => '*', 'wap_visit_histories' => '*', 'word_visit_histories' => '*', 'gdt_configs' => '*',
        'partner_urls' => '*', 'export_histories' => '*'],

    'producter' => ['dashboard' => '*', 'product_channels' => '*', 'users' => '*', 'devices' => ['index', 'edit', 'update'],
        'orders' => '*', 'products' => '*', 'weixin_menu_templates' => '*', 'weixin_menus' => '*', 'weixin_sub_menus' => '*',
        'sms_channels' => '*', 'soft_versions' => '*', 'client_themes' => '*', 'push_messages' => '*', 'partners' => '*',
        'channel_soft_versions' => '*', 'gifts' => '*', 'emoticon_images' => '*', 'audios' => "*", 'room_themes' => '*',
        'musics' => '*', 'banners' => '*', 'complaints' => '*', 'sms_histories' => '*', 'rooms' => '*', 'broadcasts' => '*',
        'share_histories' => '*', 'audio_chapters' => '*', 'payment_channels' => '*', 'product_groups' => '*', 'product_menus' => '*',
        'room_tags' => '*', 'room_categories' => '*'
    ],

    'product_activity_operator' => ['dashboard' => '*', 'users' => ['index', 'day_rank_list', 'week_rank_list', 'total_rank_list'],
        'rooms' => ['index', 'auto_hot'], 'push_messages' => '*', 'unions' => ['day_rank_list', 'week_rank_list'],
        'draw_histories' => ['day_stat', 'hour_stat'], 'gift_stats' => ['days'], 'gifts' => '*', 'emoticon_images' => '*', 'banners' => '*',
        'activities' => '*'],

    'product_operator' => ['dashboard' => '*', 'users' => '*', 'devices' => ['index', 'edit', 'update'],
        'product_channels' => '*', 'products' => '*', 'push_messages' => '*', 'export_histories' => ['download'],
        'channel_soft_versions' => '*', 'word_visit_histories' => '*',
        'weixin_kefu_messages' => '*', 'weixin_template_messages' => '*', 'ge_tui_messages' => '*',
        'weixin_menu_templates' => '*', 'weixin_menus' => '*', 'weixin_sub_menus' => '*', 'id_card_auths' => '*',
        'unions' => ['day_rank_list', 'week_rank_list', 'index', 'edit', 'update', 'family', 'total_rank_list', 'rooms', 'users_rank'],
        'gifts' => '*', 'emoticon_images' => '*', 'banners' => '*',
        'activities' => '*', 'rooms' => ['index', 'auto_hot', 'detail', 'add_user_agreement', 'delete_user_agreement', 'edit', 'update',
            'types', 'update_types', 'hot_room_score', 'hot_room_amount_score', 'hot_room_num_score'], 'room_stats' => ['day_stat'],
        'room_themes' => '*', 'broadcasts' => '*', 'albums' => '*', 'account_histories' => ['index'], 'gold_histories' => ['index'],
        'gift_orders' => ['detail'], 'user_gifts' => ['index'], 'voice_calls' => ['index'], 'union_histories' => ['basic'], 'hi_coin_histories' => ['basic', 'index'],
        'withdraw_histories' => ['basic'], 'activity_histories' => ['basic'], 'withdraw_accounts' => ['index'],
        'payments' => ['index'], 'audios' => '*', 'audio_chapters' => '*', 'product_menus' => '*', 'room_tags' => '*', 'room_categories' => '*',
        'gift_stats' => ['days'], 'draw_histories' => ['day_stat', 'hour_stat', 'index']
    ],
    'product_operator_assistant' => ['dashboard' => '*', 'users' => ['index', 'detail', 'edit', 'update'], 'rooms' => '*',
        'push_messages' => '*', 'weixin_kefu_messages' => '*', 'weixin_template_messages' => '*', 'banned_words' => ' *',
        'gifts' => '*', 'emoticon_images' => '*', 'banners' => '*', 'activities' => '*'
    ]
];