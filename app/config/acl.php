<?php
return [
    'admin' => '*',

    'editor' => ['dashboard' => '*', 'weixin_menu_templates' => '*', 'push_messages' => '*', 'weixin_template_messages' => '*'],

    'customer' => ['dashboard' => '*', 'users' => ['index', 'detail', 'basic', 'friend_list', 'followers', 'avatar'], 'id_card_auths' => '*'],

    'tester' => ['dashboard' => '*', 'users' => '*', 'devices' => ['index', 'edit', 'update', 'white_list', 'add_white_list', 'delete_white_list'],
        'product_channels' => ['index', 'edit', 'update', 'push'], 'sms_histories' => '*', 'orders' => '*', 'gift_orders' => '*', 'voice_calls' => '*', 'albums' => ['index', 'show'],
        'rooms' => ['index', 'detail'], 'soft_versions' => '*', 'payment_channels' => ['index', 'product_channels', 'edit', 'update',],
        'account_histories' => '*'
    ],

    'operator' => ['dashboard' => '*', 'stats' => ['hours', 'days'], 'wap_visits' => '*', 'word_visits' => '*', 'wap_visit_histories' => '*', 'word_visit_histories' => '*'],

    'operat_manager' => ['dashboard' => '*', 'stats' => ['hours', 'days'], 'partners' => '*',
        'operators' => ['index', 'partners', 'update_partners'], 'channel_soft_versions' => '*',
        'wap_visits' => '*', 'word_visits' => '*', 'wap_visit_histories' => '*', 'word_visit_histories' => '*', 'gdt_configs' => '*',
        'partner_urls' => '*'],

    'producter' => ['dashboard' => '*', 'product_channels' => '*', 'users' => '*', 'devices' => ['index', 'edit', 'update'],
        'orders' => '*', 'products' => '*', 'weixin_menu_templates' => '*', 'weixin_menus' => '*', 'weixin_sub_menus' => '*',
        'sms_channels' => '*', 'soft_versions' => '*', 'client_themes' => '*', 'push_messages' => '*', 'partners' => '*',
        'channel_soft_versions' => '*', 'gifts' => '*', 'emoticon_images' => '*', 'audios' => "*", 'room_themes' => '*',
        'musics' => '*', 'banners' => '*', 'complaints' => '*', 'sms_histories' => '*', 'rooms' => '*', 'broadcasts' => '*',
        'share_histories' => '*', 'audio_chapters' => '*', 'payment_channels' => '*', 'product_groups' => '*'
    ],

    'product_operator' => ['dashboard' => '*', 'users' => '*', 'devices' => ['index', 'edit', 'update'],
        'product_channels' => '*', 'products' => '*', 'push_messages' => '*', 'export_histories' => ['download'],
        'orders' => '*', 'channel_soft_versions' => '*', 'wap_visits' => '*',
        'word_visits' => '*', 'wap_visit_histories' => '*', 'word_visit_histories' => '*', 'partners' => '*',
        'weixin_kefu_messages' => '*', 'weixin_template_messages' => '*', 'ge_tui_messages' => '*',
        'weixin_menu_templates' => '*', 'weixin_menus' => '*', 'weixin_sub_menus' => '*'
    ],

    'product_operator_assistant' => ['dashboard' => '*', 'users' => ['index', 'detail', 'edit', 'update'], 'rooms' => '*',
        'push_messages' => '*', 'weixin_kefu_messages' => '*', 'weixin_template_messages' => '*', 'banned_words' => ' *',
        'gifts' => '*', 'emoticon_images' => '*', 'banners' => '*', 'activities' => '*'
    ]
];