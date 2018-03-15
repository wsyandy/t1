<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 04/01/2018
 * Time: 16:40
 */
class MusicsTask extends \Phalcon\Cli\Task
{
    function uploadAction()
    {

        $music_file = APP_ROOT . "temp/music.zip";
        $dest_file = APP_NAME . "/musics/" . uniqid() . '.zip';
        $res = StoreFile::upload($music_file, $dest_file);
    }

    function parseMusicFileAction()
    {
        $cond = [
            'conditions' => 'user_type = :user_type: and avatar_status = :avatar_status:',
            'bind' => ['user_type' => USER_TYPE_SILENT, 'avatar_status' => AUTH_SUCCESS]
        ];

        $users = Users::find($cond);

        $user_ids = [];

        foreach ($users as $user) {
            $user_ids[] = $user->id;
        }

        if (count($user_ids) < 1) {
            echoLine("no user");
            return;
        }

        $music_files = glob(APP_ROOT . "temp/100首音乐/*");

        foreach ($music_files as $music_file) {
            $file_size = filesize($music_file);
            $file_md5 = filesize($music_file);
            $dest_filename = APP_NAME . '/musics/file/' . uniqid() . '.mp3';

            $res = StoreFile::upload($music_file, $dest_filename);
            $music_name = basename($music_file);

            echoLine($music_name, $file_md5, $file_size);

            if ($res) {
                $music_name = explode("-", $music_name);
                if (count($music_name) < 2) {
                    $author = $music_name[0];
                    $name = $music_name[0];
                } else {
                    $author = $music_name[0];
                    $name = $music_name[1];
                }

                $user_id = $user_ids[array_rand($user_ids)];

                $music = new Musics();
                $music->user_id = $user_id;
                $music->singer_name = $author;
                $music->name = $name;
                $music->type = mt_rand(1, 2);
                $music->status = STATUS_ON;
                $music->rank = 1;
                $music->hot = 1;
                $music->file = $dest_filename;
                $music->file_size = $file_size;
                $music->file_md5 = $file_md5;
                $music->save();
            } else {
                echoLine("upload_file", $music_name);
            }
        }
    }
}
