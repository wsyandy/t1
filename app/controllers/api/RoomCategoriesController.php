<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/23
 * Time: 下午1:34
 */

namespace api;

class RoomCategoriesController extends BaseController
{
    function indexAction()
    {
        $room_categories = [
            [
                'name' => '娱乐',
                'second_categories' => [
                    [
                        'id' => 2,
                        'name' => '唱歌',
                    ],
                    [
                        'id' => 3,
                        'name' => '唱歌1',
                    ],
                    [
                        'id' => 4,
                        'name' => '唱歌2',
                    ],
                    [
                        'id' => 5,
                        'name' => '唱歌3',
                    ],
                    [
                        'id' => 6,
                        'name' => '唱歌4',
                    ]
                ]
            ],
            [
                'name' => '娱乐',
                'second_categories' => [
                    [
                        'id' => 8,
                        'name' => '唱歌',
                    ],
                    [
                        'id' => 9,
                        'name' => '唱歌1',
                    ],
                    [
                        'id' => 10,
                        'name' => '唱歌2',
                    ],
                    [
                        'id' => 11,
                        'name' => '唱歌3',
                    ],
                    [
                        'id' => 12,
                        'name' => '唱歌4',
                    ]
                ]
            ],
            [
                'name' => '娱乐',
                'second_categories' => [
                    [
                        'id' => 14,
                        'name' => '唱歌',
                    ],
                    [
                        'id' => 15,
                        'name' => '唱歌1',
                    ],
                    [
                        'id' => 16,
                        'name' => '唱歌2',
                    ],
                    [
                        'id' => 17,
                        'name' => '唱歌3',
                    ],
                    [
                        'id' => 18,
                        'name' => '唱歌4',
                    ]
                ]
            ]
        ];

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['room_categories' => $room_categories]);
    }
}