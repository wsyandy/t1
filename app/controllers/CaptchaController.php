<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/7/14
 * Time: 17:43
 */
class CaptchaController extends ApplicationController
{
    function indexAction()
    {
        info($this->remoteIp(), $this->request->getUserAgent());

        $hot_cache = Users::getHotWriteCache();

        $string = "abcdefghijkmnpqrstuvwxyz23456789";
        $str = "";
        $len = strlen($string);

        for ($i = 0; $i < 4; $i++) {
            $pos = rand(0, $len - 1);
            $str .= $string{$pos};
        }


        $image_token = uuid();

        $hot_cache->setex('image_token_' . $image_token, 60 * 10, $str);

        $img_handle = imagecreate(60, 20);
        $back_color = imagecolorallocate($img_handle, 255, 255, 255); //背景颜色（白色）
        $txt_color = imagecolorallocate($img_handle, rand(0, 255), rand(0, 255), rand(0, 255));  //文本颜色（黑色）

        //加入干扰线
        $line = imagecolorallocate($img_handle, rand(0, 255), rand(0, 255), rand(0, 255));
        imageline($img_handle, rand(0, 15), rand(0, 15), rand(100, 150), rand(10, 50), $line);

        //加入干扰象素
        for ($i = 0; $i < 200; $i++) {
            $randcolor = imagecolorallocate($img_handle, rand(0, 255), rand(0, 255), rand(0, 255));
            imagesetpixel($img_handle, rand() % 100, rand() % 50, $randcolor);
        }

        imagefill($img_handle, 0, 0, $back_color);             //填充图片背景色
        imageString($img_handle, 28, 10, 0, $str, $txt_color);//水平填充一行字符串

        ob_start();

        imagepng($img_handle);
        imagedestroy($img_handle);
        $image_data = base64_encode(ob_get_contents());

        ob_end_clean();

        $this->renderJSON(ERROR_CODE_SUCCESS, '',
            ['image_token' => $image_token, 'image_data' => "data:image/png;base64," . $image_data]);
    }
}