<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/9
 * Time: 19:45
 */

class BoomHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type Users
     */
    private $_boom_user;

    /**
     * @type Rooms
     */
    private $_room;

    static $TYPE = [BOOM_HISTORY_GIFT_TYPE => '礼物', BOOM_HISTORY_DIAMOND_TYPE => '钻石', BOOM_HISTORY_GOLD_TYPE => '金币'];

    function afterCreate()
    {

    }

    public function getGiftName()
    {
        if ($this->target_id == 0) {
            return null;
        }

        $gift = Gifts::findFirstById($this->target_id);
        return $gift->name;
    }

    //引爆者礼物
    static function randomBoomUserGiftId($user, $room, $opts = [])
    {
        $boom_num = fetch($opts, 'boom_num');
        if ($boom_num > 3) {
            $boom_num = 3;
        }

        $gift_ids = [1 => 74, 2 => 87, 3 => 103];

        if (isDevelopmentEnv()) {
            $gift_ids = [1 => 42, 2 => 162, 3 => 175];
        }

        $gift_id = fetch($gift_ids, $boom_num, 1);
        return $gift_id;
    }

    //贡献这礼物 rank 用户贡献的排名
    static function randomContributionUserGiftIdByRank($user, $room, $opts = [])
    {
        $cache = self::getHotWriteCache();
        $type = 'contribution_gift';
        $boom_num = fetch($opts, 'boom_num');
        $rank = fetch($opts, 'rank');

        if ($boom_num < 3 && $rank > 2) {
            info($boom_num, $rank);
            return null;
        }

        if ($boom_num > 3) {
            $boom_num = 3;
        }

        $datas = [
            1 => [
                1 => ['id' => 19, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 1, 'target_id' => 104, 'number' => 1]
            ],
            2 => [
                1 => ['id' => 20, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 1, 'target_id' => 105, 'number' => 1]
            ],
            3 => [
                1 => ['id' => 21, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 1, 'target_id' => 104, 'number' => 1],
                2 => ['id' => 22, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 1, 'target_id' => 105, 'number' => 1]
            ]
        ];

        if (isDevelopmentEnv()) {
            $datas = [
                1 => [
                    1 => ['id' => 19, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 1, 'target_id' => 172, 'number' => 1]
                ],
                2 => [
                    1 => ['id' => 20, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 1, 'target_id' => 174, 'number' => 1]
                ],
                3 => [
                    1 => ['id' => 21, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 1, 'target_id' => 172, 'number' => 1],
                    2 => ['id' => 22, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 1, 'target_id' => 174, 'number' => 1]
                ]
            ];
        }

        $data = fetch($datas, $boom_num);
        $gift_datas = [];

        foreach ($data as $datum) {
            $res = self::isLimit($room, $datum, ['boom_num' => $boom_num, 'type' => $type]);
            if ($res) {
                info($datum);
                continue;
            }
            $gift_datas[] = $datum;
        }

        if (isBlank($gift_datas)) {
            return null;
        }

        $gift_data = $gift_datas[array_rand($gift_datas)];
        self::recordPrizeNum($room, $gift_data, ['boom_num' => $boom_num, 'type' => $type]);
        return $gift_data;
    }

    //有段位礼物
    static function userSegmentGift($user, $room, $opts = [])
    {
        $type = 'user_segment_gift';
        $boom_num = fetch($opts, 'boom_num');

        if ($boom_num > 3) {
            $boom_num = 3;
        }

        $datas = [
            1 => [
                1 => ['id' => 23, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 30, 'target_id' => 106, 'number' => 1],
                2 => ['id' => 24, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 30, 'target_id' => 109, 'number' => 1],
            ],
            2 => [
                1 => ['id' => 25, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 30, 'target_id' => 107, 'number' => 1],
                2 => ['id' => 26, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 30, 'target_id' => 111, 'number' => 1],
            ],
            3 => [
                1 => ['id' => 27, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 30, 'target_id' => 108, 'number' => 1],
                2 => ['id' => 28, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 30, 'target_id' => 110, 'number' => 1],
            ]
        ];

        if (isDevelopmentEnv()) {
            $datas = [
                1 => [
                    1 => ['id' => 23, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 2, 'target_id' => 181, 'number' => 1],
                    2 => ['id' => 24, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 2, 'target_id' => 182, 'number' => 1],
                ],
                2 => [
                    1 => ['id' => 25, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 2, 'target_id' => 184, 'number' => 1],
                    2 => ['id' => 26, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 2, 'target_id' => 183, 'number' => 1],
                ],
                3 => [
                    1 => ['id' => 27, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 2, 'target_id' => 185, 'number' => 1],
                    2 => ['id' => 28, 'type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 2, 'target_id' => 186, 'number' => 1],
                ]
            ];
        }

        $data = fetch($datas, $boom_num);
        $gift_datas = [];

        foreach ($data as $datum) {
            $res = self::isLimit($room, $datum, ['boom_num' => $boom_num, 'type' => $type]);
            if ($res) {
                info($datum);
                continue;
            }
            $gift_datas[] = $datum;
        }

        if (isBlank($gift_datas)) {
            return null;
        }

        $gift_data = $gift_datas[array_rand($gift_datas)];
        self::recordPrizeNum($room, $gift_data, ['boom_num' => $boom_num, 'type' => $type]);
        return $gift_data;
    }

    //$num 爆礼物次数
    static function randomBoomGiftIdByBoomNum($user, $room, $opts = [])
    {
        $type = 'random_diamond';
        $boom_num = fetch($opts, 'boom_num');

        if ($boom_num > 3) {
            $boom_num = 3;
        }

        $datas = [
            1 => [
                1 => ['id' => 1, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 20, 'number' => 5],
                2 => ['id' => 2, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 20, 'number' => 10],
                3 => ['id' => 3, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 15, 'number' => 15],
                4 => ['id' => 4, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 15, 'number' => 20],
                5 => ['id' => 5, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 15, 'number' => 25],
                6 => ['id' => 6, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 15, 'number' => 30],
            ],
            2 => [
                1 => ['id' => 7, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 20, 'number' => 5],
                2 => ['id' => 8, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 20, 'number' => 10],
                3 => ['id' => 9, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 15, 'number' => 15],
                4 => ['id' => 10, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 15, 'number' => 20],
                5 => ['id' => 11, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 15, 'number' => 25],
                6 => ['id' => 12, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 15, 'number' => 30],
            ],
            3 => [
                1 => ['id' => 13, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 20, 'number' => 5],
                2 => ['id' => 14, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 20, 'number' => 10],
                3 => ['id' => 15, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 15, 'number' => 15],
                4 => ['id' => 16, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 15, 'number' => 20],
                5 => ['id' => 17, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 15, 'number' => 25],
                6 => ['id' => 18, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 15, 'number' => 30],
            ]
        ];

        if (isDevelopmentEnv()) {
            $datas = [
                1 => [
                    1 => ['id' => 1, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 2, 'number' => 5],
                    2 => ['id' => 2, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 2, 'number' => 10],
                    3 => ['id' => 3, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 1, 'number' => 15],
                    4 => ['id' => 4, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 1, 'number' => 20],
                    5 => ['id' => 5, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 1, 'number' => 25],
                    6 => ['id' => 6, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 1, 'number' => 30],
                ],
                2 => [
                    1 => ['id' => 7, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 2, 'number' => 5],
                    2 => ['id' => 8, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 2, 'number' => 10],
                    3 => ['id' => 9, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 1, 'number' => 15],
                    4 => ['id' => 10, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 1, 'number' => 20],
                    5 => ['id' => 11, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 1, 'number' => 25],
                    6 => ['id' => 12, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 1, 'number' => 30],
                ],
                3 => [
                    1 => ['id' => 13, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 2, 'number' => 5],
                    2 => ['id' => 14, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 2, 'number' => 10],
                    3 => ['id' => 15, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 1, 'number' => 15],
                    4 => ['id' => 16, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 1, 'number' => 20],
                    5 => ['id' => 17, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 1, 'number' => 25],
                    6 => ['id' => 18, 'type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 1, 'number' => 30],
                ]
            ];
        }

        $data = fetch($datas, $boom_num);
        $gift_datas = [];

        foreach ($data as $datum) {
            $res = self::isLimit($room, $datum, ['boom_num' => $boom_num, 'type' => $type]);
            if ($res) {
                info($datum);
                continue;
            }
            $gift_datas[] = $datum;
        }

        if (isBlank($gift_datas)) {
            return null;
        }

        $gift_data = $gift_datas[array_rand($gift_datas)];
        self::recordPrizeNum($room, $gift_data, ['boom_num' => $boom_num, 'type' => $type]);
        return $gift_data;
    }

    static function isLimit($room, $data, $opts = [])
    {
        $cache = self::getHotWriteCache();
        $id = fetch($data, 'id');
        $boom_num = fetch($opts, 'boom_num');
        $type = fetch($opts, 'type');
        $total_number = fetch($data, 'total_number');
        $num = $boom_num;
        $key = "boom_gift_hit_num_room_id{$room->id}" . "_{$id}_boom_num_" . $num . "_type_" . $type;
        $hit_num = $cache->get($key);
        info($room->id, $key, $hit_num, $data, $opts);
        if ($hit_num >= $total_number) {
            return true;
        }
        return false;
    }

    static function randomDiamond($total_value)
    {
        $total_amount = intval($total_value * 0.05);
        $gift_ids = [64 => 1, 65 => 5, 66 => 50, 47 => 188, 92 => 20, 93 => 10, 94 => 100];
        $gift_amount = array_count_values($gift_ids);
        $total_amount = $total_amount - $gift_amount;
        $amount = intval($total_amount / 100);
        return $amount;
    }

    static function recordPrizeNum($room, $gift_data, $opts = [])
    {
        $cache = self::getHotWriteCache();
        $boom_num = fetch($opts, 'boom_num');
        $type = fetch($opts, 'type');
        $id = fetch($gift_data, 'id');
        $key = "boom_gift_hit_num_room_id{$room->id}" . "_{$id}_boom_num_" . $boom_num . "_type_" . $type;
        $cache->incrby($key, 1);
        $cache->expire($key, 600);
    }

    /**
     * 爆礼物id 倒叙日志排行
     * @param $user_id
     * @param int $per_page
     * @return PaginationModel
     */
    static public function findHistoriesByRoom($room, $per_page = 10)
    {
        $conditions = [
            'conditions' => 'room_id = :room_id: and created_at >= :start: and created_at <= :end:',
            'bind' => ['room_id' => $room->id, 'start' => time() - 600, 'end' => time()],
            'order' => 'id asc'
        ];

        $list = BoomHistories::findPagination($conditions, 1, $per_page);
        return $list;
    }

    static public function findHistoriesByUser($user, $per_page = 10)
    {
        $conditions = [
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user->id],
            'order' => 'id desc'
        ];

        $list = BoomHistories::findPagination($conditions, 1, $per_page);
        return $list;
    }


    /**
     * @return array
     */
    public function toSimpleJson()
    {

        if ($this->type != BOOM_HISTORY_GIFT_TYPE) {

            if ($this->type == BOOM_HISTORY_DIAMOND_TYPE) {
                $name = '钻石';
                $image_url = Backpacks::getDiamondImage();
            } else {
                $name = '金币';
                $image_url = Backpacks::getGoldImage();
            }

        } else {

            $target = Gifts::findFirstById($this->target_id);
            $image_url = $target->image_url;
            $name = $target->name;

        }

        return ['user' => $this->user->nickname, 'name' => $name, 'number' => $this->number, 'image_url' => $image_url];
    }

    static public function createBoomHistory($user, $room, $opts)
    {
        $target_id = fetch($opts, 'target_id');
        $number = fetch($opts, 'number');
        $type = fetch($opts, 'type');
        $room_id = fetch($opts, 'room_id');

        $boom_history = new BoomHistories();

        if (isBlank($type) || isBlank($number)) {
            return [ERROR_CODE_FAIL, '参数错误', null];
        }

        if (isBlank($target_id) && $type == BOOM_HISTORY_GIFT_TYPE) {
            return [ERROR_CODE_FAIL, '参数错误', null];
        }

        $boom_amount = fetch($opts, 'boom_amount');
        $boom_num = fetch($opts, 'boom_num');
        $pay_amount = fetch($opts, 'pay_amount');
        $boom_user_id = fetch($opts, 'boom_user_id');
        $amount = $number;
        $gift = null;

        if (BOOM_HISTORY_GIFT_TYPE == $type) {
            $gift = Gifts::findFirstById($target_id);
            if ($gift) {
                $amount = $gift->amount;
                $boom_history->gift = $gift;
            }
        }

        $boom_history->user_id = $user->id;
        $boom_history->target_id = $target_id;
        $boom_history->type = $type;
        $boom_history->number = $number;
        $boom_history->room_id = $room_id;
        $boom_history->boom_amount = $boom_amount;
        $boom_history->boom_num = $boom_num;
        $boom_history->pay_amount = $pay_amount;
        $boom_history->boom_user_id = $boom_user_id;
        $boom_history->amount = $amount;
        $boom_history->user = $user;
        $boom_history->room = $room;

        if ($boom_history->save()) {

            if ($type == BOOM_HISTORY_GIFT_TYPE) {

                if (isBlank($target_id)) {
                    return [ERROR_CODE_FAIL, '', null];
                }

                $gift = $boom_history->gift;

                if ($gift) {
                    if ($gift->isCar()) {
                        GiftOrders::asyncCreateGiftOrder(SYSTEM_ID, [$user->id], $gift->id, ['remark' => '爆礼物', 'type' => GIFT_ORDER_TYPE_BOOM_GIFT]);
                        if (!$gift->isNormal()) {
                            $content = "恭喜【{$user->nickname}】在爆火箭中获得了超超超绝版座驾[{$gift->name}]";
                            Rooms::delay()->asyncAllNoticePush($content, ['hot' => 1, 'type' => 'top_topic_message']);
                        }
                    } else {
                        if (!Backpacks::createBackpack($user, ['target_id' => $target_id, 'number' => $number, 'type' => BACKPACK_GIFT_TYPE])) {
                            return [ERROR_CODE_FAIL, '加入背包失败', null];
                        }
                        if (!$gift->isNormal()) {
                            $content = "恭喜【{$user->nickname}】在爆火箭中获得了超超超绝版礼物[{$gift->name}]";
                            Rooms::delay()->asyncAllNoticePush($content, ['hot' => 1, 'type' => 'top_topic_message']);
                        }
                    }
                }
            } elseif ($type == BOOM_HISTORY_DIAMOND_TYPE) {

                $opts['remark'] = '爆礼物获得' . $number . '钻石';
                \AccountHistories::changeBalance($user, ACCOUNT_TYPE_IN_BOOM, $number, $opts);

            } elseif ($type == BOOM_HISTORY_GOLD_TYPE) {

                $opts['remark'] = '爆礼物获得' . $number . '金币';
                \GoldHistories::changeBalance($user, GOLD_TYPE_IN_BOOM, $number, $opts);

            }

            return [ERROR_CODE_SUCCESS, '', $boom_history];
        }
    }

    static public function getBoomDiamondOrGold($type, $number)
    {

        if ($type == BOOM_HISTORY_DIAMOND_TYPE) {
            $name = '钻石';
            $image = \Backpacks::getDiamondImage();
        } else {
            $name = '金币';
            $image = \Backpacks::getGoldImage();
        }

        // 嵌套array 返回接口固定结构数据
        $target = [

            [
                'name' => $name,
                'image_url' => $image,
                'number' => $number
            ]
        ];

        return $target;
    }

    /**
     * 记录爆礼物抽中的奖品
     * @param $user_id
     * @param $room_id
     * @return string
     */
    static public function generateBoomUserSignKey($user_id, $room_id)
    {
        return 'boom_target_room_' . $room_id . '_user_' . $user_id;
    }

    static function getPrize($user, $room)
    {
        $room_id = $room->id;

        // 没爆礼物不抽奖
        $expire_at = \Rooms::getBoomGiftExpireAt($room_id);

        if (isBlank($expire_at)) {
            return [ERROR_CODE_FAIL, '未开始爆礼物', null];
        }

        // 抽奖物品保存至爆礼物结束时间
        $expire = $expire_at - time();
        $expire = $expire > 180 ? 180 : ($expire < 0 ? 1 : $expire);

        // 爆出的礼物从缓存拿到
        $cache = \BoomHistories::getHotWriteCache();
        $user_sign_key = \BoomHistories::generateBoomUserSignKey($user->id, $room_id);
        $user_sign = $cache->get($user_sign_key);
        if ($user_sign == 1) {
            return [ERROR_CODE_FAIL, '已领取！', null];
        }

        $boom_gift_time = \Rooms::getBoomGiftTime($room_id);
        //用户贡献值 控制概率
        $record_key = \Rooms::generateBoomRecordDayKey($room_id, $boom_gift_time);
        $pay_amount = $cache->zscore($record_key, $user->id);
        $boom_user_id = $room->getBoomUserId();
        $boom_amount = $room->getCurrentBoomGiftValue($boom_gift_time);
        $boom_num = $room->getBoomNum($boom_gift_time);
        $type = BOOM_HISTORY_GIFT_TYPE;
        $target_id = 0;
        $number = 1;

        $lock = tryLock("boom_room_lock_" . $room_id);
        if ($boom_user_id == $user->id) {
            $gift_id = \BoomHistories::randomBoomUserGiftId($user, $room, ['boom_num' => $boom_num]);
            $target_id = $gift_id;
            info("boom_user", $user->id, $boom_num, $target_id, $boom_user_id);
        } elseif ($pay_amount > 0) {
            $rank = $cache->zrank($record_key, $user->id) + 1;
            $data = [];
            if ($rank && $rank > 0 && $rank <= 3) {
                $data = \BoomHistories::randomContributionUserGiftIdByRank($user, $room, ['rank' => $rank, 'boom_num' => $boom_num]);
            }

            if (!$data) {
                if ($user->segment) {
                    $data = \BoomHistories::userSegmentGift($user, $room, ['boom_num' => $boom_num]);

                    if (!$data) {
                        $data = \BoomHistories::randomBoomGiftIdByBoomNum($user, $room, ['boom_num' => $boom_num]);
                    }
                } else {
                    $data = \BoomHistories::randomBoomGiftIdByBoomNum($user, $room, ['boom_num' => $boom_num]);
                }
            }

            if (!$data) {
                unlock($lock);
                return [ERROR_CODE_FAIL, '亲，奖品已经领完了，你的反应有点慢哦', null];
            }

            $type = fetch($data, 'type');
            $target_id = fetch($data, 'target_id');
            $number = fetch($data, 'number');
            info("contribution_user", $user->id, $user->uid, $user->segment, $pay_amount, $rank, $target_id, $type, $number);
        } else {
            if ($user->segment) {
                $data = \BoomHistories::userSegmentGift($user, $room, ['boom_num' => $boom_num]);

                if (!$data) {
                    $data = \BoomHistories::randomBoomGiftIdByBoomNum($user, $room, ['boom_num' => $boom_num]);
                }
                
            } else {
                $data = \BoomHistories::randomBoomGiftIdByBoomNum($user, $room, ['boom_num' => $boom_num]);
            }

            if (!$data) {
                unlock($lock);
                return [ERROR_CODE_FAIL, '亲，奖品已经领完了，你的反应有点慢哦', null];
            }

            $type = fetch($data, 'type');
            $target_id = fetch($data, 'target_id');
            $number = fetch($data, 'number');
        }

        info("boom_record", "用户id:", $user->id, 'uid', $user->uid, "贡献值:", $pay_amount, "房间id:", $room_id, "个数", $number, 'type', $type, $target_id);

        $res = \BoomHistories::createBoomHistory($user, $room,
            ['target_id' => $target_id, 'type' => $type, 'number' => $number, 'room_id' => $room_id, 'boom_user_id' => $boom_user_id,
                'boom_amount' => $boom_amount, 'boom_num' => $boom_num, 'pay_amount' => $pay_amount]);

        list($code, $reason, $boom_history) = $res;

        if ($code == ERROR_CODE_FAIL) {
            unlock($lock);
            return [ERROR_CODE_FAIL, '领取失败', null];
        }

        $cache->setex($user_sign_key, $expire, 1);
        unlock($lock);

        return [$code, $reason, $boom_history];
    }
}