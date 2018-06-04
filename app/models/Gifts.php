<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/1/2
 * Time: 下午10:13
 */
class Gifts extends BaseModel
{

    //礼物支付类型
    static $PAY_TYPE = [GIFT_PAY_TYPE_DIAMOND => '钻石', GIFT_PAY_TYPE_GOLD => '金币'];

    //礼物类型 暂定
    static $TYPE = [GIFT_TYPE_COMMON => '普通礼物', GIFT_TYPE_CAR => '座驾'];

    //礼物状态
    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    //渲染类型
    static $RENDER_TYPE = ['gif' => 'gif', 'svga' => 'svga'];

    //图片文件
    static $files = ['image' => APP_NAME . '/gifts/image/%s', 'big_image' => APP_NAME . '/gifts/big_image/%s',
        'dynamic_image' => APP_NAME . '/gifts/dynamic_image/%s', 'svga_image' => APP_NAME . '/gifts/svga_image/%s',
        'music' => APP_NAME . '/gifts/music/%s'];


    function beforeCreate()
    {
        $this->status = STATUS_OFF;
    }


    function afterCreate()
    {
        if ($this->svga_image) {
            self::uploadLock();
            self::delay()->zipSvgaImage($this->id);
        }
    }

    function afterUpdate()
    {
        if ($this->hasChanged('svga_image')) {
            self::uploadLock();
            self::delay()->zipSvgaImage($this->id);
        }
    }

    function isDiamondPayType()
    {
        return GIFT_PAY_TYPE_DIAMOND == $this->pay_type;
    }

    function isGoldPayType()
    {
        return GIFT_PAY_TYPE_GOLD == $this->pay_type;
    }

    function isCar()
    {
        return GIFT_TYPE_CAR == $this->type;
    }

    static function uploadLock()
    {
        $hot_cache = self::getHotWriteCache();
        $hot_cache->setex('upload_gift_svga_image_lock', 10, 1);
    }

    static function uploadUnLock()
    {
        $hot_cache = self::getHotWriteCache();
        $hot_cache->del('upload_gift_svga_image_lock');
    }

    static function hasUploadLock()
    {
        $hot_cache = self::getHotWriteCache();
        return $hot_cache->get('upload_gift_svga_image_lock') > 0;
    }

    static function zipSvgaImage($gift_id)
    {
        $zip_gift = Gifts::findFirstById($gift_id);

        debug($zip_gift->getSvgaImageName());

        $gifts = Gifts::findBy(['render_type' => 'svga']);

        if (count($gifts) < 1) {
            debug("no svga");
            return;
        }

        $dir_name = APP_ROOT . "temp/gift_svga_images";
        checkDirExists($dir_name);
        $dest_filenames = [];

        foreach ($gifts as $gift) {

            if (!$gift->getSvgaImageUrl()) {
                info("svga_image_not_exists", $gift->id);
                continue;
            }

            try {

                $dest_filename = httpSave($gift->getSvgaImageUrl(), $dir_name . "/" . $gift->getSvgaImageName());
                if (!$dest_filename) {
                    continue;
                }

                $dest_filenames[] = $dest_filename;

            } catch (Exception $exception) {
                info("Exce", $gift->id);
            }
        }

        $zip_filename = APP_ROOT . "temp/" . uniqid() . ".zip";
        $zip = new ZipArchive();
        $zip->open($zip_filename, ZipArchive::CREATE);   //打开压缩包

        foreach ($dest_filenames as $dest_filename) {
            if (file_exists($dest_filename)) {
                debug($dest_filename, basename($dest_filename));
                $zip->addFile($dest_filename, basename($dest_filename));   //向压缩包中添加文件
                //unlink($dest_filename);
            }
        }

        $zip->close();  //关闭压缩包

        foreach ($dest_filenames as $dest_filename) {
            if (file_exists($dest_filename)) {
                unlink($dest_filename);
            }
        }

        $resource_file = APP_NAME . "/gift_resources/resource_file/" . uniqid() . ".zip";
        $res = StoreFile::upload($zip_filename, $resource_file);

        if ($res) {
            $old_gift_resource = GiftResources::findFirst(['order' => 'resource_code desc']);
            $resource_code = 0;

            if ($old_gift_resource) {
                $resource_code = $old_gift_resource->resource_code;
            }

            $gift_resource = new GiftResources();
            $gift_resource->resource_file = $resource_file;
            $gift_resource->status = STATUS_ON;
            $gift_resource->resource_code = $resource_code + 1;
            $gift_resource->remark = $zip_gift->name . "更新";
            $gift_resource->save();
        }

        if (file_exists($zip_filename)) {
            unlink($zip_filename);
        }

        Gifts::uploadUnLock();
    }

    function mergeJson()
    {
        return [
            'image_url' => $this->image_url,
            'image_small_url' => $this->image_small_url,
            'image_big_url' => $this->image_big_url,
            'dynamic_image_url' => $this->dynamic_image_url,
            'svga_image_name' => $this->svga_image_name,
            'svga_image_url' => $this->svga_image_url,
            'platform_num' => $this->platform_num,
            'product_channel_num' => $this->product_channel_num
        ];
    }

    function toSimpleJson()
    {
        $opts = [
            'id' => $this->id,
            'image_url' => $this->image_url,
            'image_small_url' => $this->image_small_url,
            'image_big_url' => $this->image_big_url,
            'name' => $this->name,
            'amount' => $this->amount,
            'pay_type' => $this->pay_type,
            'gift_type' => $this->type,
            'dynamic_image_url' => $this->dynamic_image_url,
            'svga_image_name' => $this->svga_image_name,
            'render_type' => $this->render_type,
            'svga_image_url' => $this->svga_image_url,
            'expire_day' => $this->expire_day,
            'show_rank' => $this->show_rank,
            'expire_time' => $this->expire_time ? $this->expire_time : 180,
            'music_url' => $this->music_url
        ];

        if (isset($this->buy_status)) {
            $opts['buy_status'] = $this->buy_status;
        }

        return $opts;
    }

    function getMusicUrl()
    {
        if (isBlank($this->music)) {
            return '';
        }

        return StoreFile::getUrl($this->music);
    }

    function getDynamicImageUrl()
    {
        if (isBlank($this->dynamic_image)) {
            return '';
        }

        return StoreFile::getUrl($this->dynamic_image);
    }

    function getImageUrl()
    {
        if (isBlank($this->image)) {
            return '';
        }

        return StoreFile::getUrl($this->image);
    }

    function getSvgaImageUrl()
    {
        if (isBlank($this->svga_image)) {
            return '';
        }

        return StoreFile::getUrl($this->svga_image);
    }

    function getImageSmallUrl()
    {
        if (isBlank($this->image)) {
            return '';
        }

        return StoreFile::getUrl($this->image) . '@!small';
    }

    function getImageBigUrl()
    {
        if (isBlank($this->image) && isBlank($this->big_image)) {
            return '';
        }
        if (isPresent($this->big_image)) {
            return \StoreFile::getUrl($this->big_image);
        }
        return StoreFile::getUrl($this->image) . '@!big';
    }

    function getSvgaImageName()
    {
        if (!$this->svga_image) {
            return '';
        }

        $names = explode("/", $this->svga_image);

        if (count($names) < 1) {
            return '';
        }

        return end($names);
    }

    function isInvalid()
    {
        return $this->status == GIFT_STATUS_OFF;
    }

    /**
     * 获取所有的有效礼物，这里先做一个限制，最多100个
     * @return PaginationModel
     */
    static function findValidList($user, $opts = [])
    {
        $platform = $user->platform;
        $product_channel_id = $user->product_channel_id;
        $gift_type = fetch($opts, 'gift_type');

        $conditions = [
            'conditions' => "status = :status:",
            'bind' => [
                'status' => GIFT_STATUS_ON
            ],
            'order' => 'rank desc, amount asc'
        ];

        $conditions['conditions'] .= " and ( platforms like '*' or platforms like :platforms: or platforms = '')";
        $conditions['bind']['platforms'] = "%" . $platform . "%";

        $conditions['conditions'] .= " and (product_channel_ids = '' or product_channel_ids is null or product_channel_ids like :product_channel_ids:)";
        $conditions['bind']['product_channel_ids'] = "%," . $product_channel_id . ",%";

        if ($gift_type) {
            $conditions['conditions'] .= ' and type = :gift_type:';
            $conditions['bind']['gift_type'] = $gift_type;
        }

        $page = 1;
        $per_page = 100;

        $gifts = \Gifts::findPagination($conditions, $page, $per_page);

        //待优化次代码
        if (GIFT_TYPE_CAR == $gift_type) {

            foreach ($gifts as $gift) {
                $user_gift = UserGifts::findFirst(
                    [
                        'conditions' => 'user_id = :user_id: and gift_id = :gift_id:',
                        'bind' => ['user_id' => $user->id, 'gift_id' => $gift->id],
                        'columns' => 'id'
                    ]);
                if ($user_gift) {
                    $gift->buy_status = true;
                } else {
                    $gift->buy_status = false;
                }
            }
        }

        return $gifts;
    }

    static function generateNotifyData($opts)
    {
        $gift = fetch($opts, 'gift');
        $gift_num = fetch($opts, 'gift_num');
        $sender = fetch($opts, 'sender');
        $user = \Users::findById($opts['user_id']);
        $data = [];

        if ($gift) {
            $data = array_merge($data, $gift->toSimpleJson());
            $data['num'] = $gift_num;
            $data['user_id'] = $user->id;
            $data['user_nickname'] = $user->nickname;
            $data['user_avatar_small_url'] = $user->avatar_small_url;
            $data['sender_id'] = $sender->id;
            $data['sender_nickname'] = $sender->nickname;
            $data['sender_avatar_small_url'] = $sender->avatar_small_url;
        }

        return $data;

    }

    function expireAt()
    {
        return time() + $this->expire_time * 60;
    }

    function productChannelNum()
    {
        $num = 0;
        if ($this->product_channel_ids) {
            $product_channel_ids = explode(',', $this->product_channel_ids);
            $product_channel_ids = array_filter(array_unique($product_channel_ids));
            $num = count($product_channel_ids);
        }

        return $num;
    }

    function platformNum()
    {
        $platforms = $this->platforms;
        $num = 'all';

        if ($platforms && '*' != $platforms) {
            $platforms = array_filter(explode(',', $platforms));
            $num = count($platforms);
        }

        return $num;
    }

    static function getGiftsList($last_activity, $activity)
    {
        $last_gift_ids_array = $last_activity->getGiftIdsArray();
        $gift_ids_array = $activity->getGiftIdsArray();

        $last_gifts = \Gifts::findByIds($last_gift_ids_array);
        $gifts = \Gifts::findByIds($gift_ids_array);

        return [$last_gifts, $gifts];
    }


    /**
     * 随机n个礼物
     * @param int $max
     * @return array
     */
    static public function getNGift($max = 3)
    {
        // 获取伪索引
        $total = Gifts::count() - 1;
        if ($max >= $total) $max = 3;

        // 随机 $max 个索引
        $keys = array_rand(range(0, $total), mt_rand(1, $max));
        $keys = !is_array($keys) ? [$keys] : $keys;

        // 查询礼物
        $gifts = Gifts::findByIds($keys);
        $gifts = $gifts->toJson('gifts')['gifts'];

        // 返回的礼物列表
        $list = array();
        foreach ($gifts as $value) {
            $list[] = array(
                'id' => $value['id'],
                'name' => $value['name'],
                'image_url' => StoreFile::getUrl($value['image']),
                'number' => mt_rand(1, 5)
            );
        }

        return $list;
    }

    function isSvga()
    {
        return 'svga' == $this->render_type;
    }

    function getEffectImageUrl($root, $gift_num, $gift_amount)
    {
        if ($this->isSvga()) {
            return '';
        }

        if ($gift_amount < 500 || $gift_num < 66) {
            return '';
        }

        if ($gift_amount < 1000) {
            return $root . "images/gift_effect_image1.png";
        }

        return $root . "images/gift_effect_image2.png";
    }

    function isNormal()
    {
        return STATUS_ON == $this->status;
    }

    function isExpired()
    {
        return STATUS_OFF == $this->status;
    }
}