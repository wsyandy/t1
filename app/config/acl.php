<?php
return [
    'admin' => '*',

    'editor' => ['dashboard' => '*', 'devices' => ['white_list', 'add_white_list', 'delete_white_list'], 'emoticon_images' => '*',
        'gifts' => '*', 'audios' => '*', 'audio_chapters' => '*'],

    'customer' => ['dashboard' => '*', 'users' => '*', 'devices' => ['index'], 'sms_histories' => '*', 'orders' => '*', 'gift_orders' => '*'],

    'tester' => ['dashboard' => '*', 'users' => '*', 'devices' => ['index', 'edit', 'update', 'white_list', 'add_white_list', 'delete_white_list'],
        'product_channels' => '*', 'sms_histories' => '*', 'orders' => '*', 'gift_orders' => '*', 'push_messages' => '*', 'products' => '*'],

    'operator' => ['dashboard' => '*', 'devices' => ['index'], 'stats' => ['hours', 'days', 'partners'], 'export_histories' => ['download'],
        'wap_visits' => '*', 'word_visits' => '*', 'wap_visit_histories' => '*', 'word_visit_histories' => '*'],

    'operator_manager' => ['dashboard' => '*', 'devices' => '*', 'stats' => '*', 'partners' => '*',
        'operators' => ['index', 'partners', 'update_partners'], 'channel_soft_versions' => '*', 'export_histories' => ['download'],
        'wap_visits' => '*', 'word_visits' => '*', 'wap_visit_histories' => '*', 'word_visit_histories' => '*'],

    'producter' => ['dashboard' => '*', 'product_channels' => '*', 'users' => '*', 'devices' => ['index', 'edit', 'update'],
        'orders' => '*', 'products' => '*', 'weixin_menu_templates' => '*', 'weixin_menus' => '*', 'weixin_sub_menus' => '*',
        'sms_channels' => '*', 'soft_versions' => '*', 'client_themes' => '*', 'push_messages' => '*', 'partners' => '*',
        'channel_soft_versions' => '*',
    ],

    'product_operator' => ['dashboard' => '*', 'users' => '*', 'devices' => ['index', 'edit', 'update'],
        'product_channels' => '*', 'products' => '*', 'push_messages' => '*', 'export_histories' => ['download'],
        'orders' => '*', 'channel_soft_versions' => '*', 'stats' => ['days'], 'wap_visits' => '*',
        'word_visits' => '*', 'wap_visit_histories' => '*', 'word_visit_histories' => '*', 'partners' => '*',
        'weixin_kefu_messages' => '*', 'weixin_template_messages' => '*', 'ge_tui_messages' => '*',
        'weixin_menu_templates' => '*', 'weixin_menus' => '*', 'weixin_sub_menus' => '*',
    ],

    'product_operator_assistant' => ['dashboard' => '*']
];