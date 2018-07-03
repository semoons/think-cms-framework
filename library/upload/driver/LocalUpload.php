<?php
namespace cms\upload\driver;

use UpYun;
use cms\Common;
use cms\Upload;
use newday\common\Format;

class LocalUpload extends Upload
{

    /**
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \app\common\driver\Upload::uploadFile()
     */
    public function uploadFile($path, $target_path)
    {
        try {
            // 保存路径
            $save_path = $this->option('root') . $target_path;
            
            // 文件夹检查
            $save_dir = dirname($save_path);
            if (! is_dir($save_dir)) {
                mkdir($save_dir, 0777, true);
            }
            
            // 移动文件
            rename($path, $save_path);
            
            // 拼接链接
            $url = $this->option('url') . $target_path;
            return Format::formatResult(1, '上传成功', $url);
        } catch (\Exception $e) {
            return Format::formatResult(0, '上传文件发生意外');
        }
    }

    /**
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \app\common\driver\Upload::fetchFile()
     */
    public function fetchFile($target_url)
    {
        try {
            $target_path = str_replace($this->option('url'), '', $target_url);
            $save_path = $this->option('root') . $target_path;
            
            // 读取文件
            $content = Common::readFile($save_path);
            
            return Format::formatResult(1, '读取文件成功', $content);
        } catch (\Exception $e) {
            return Format::formatResult(0, '读取文件发生意外');
        }
    }

    /**
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \app\common\driver\Upload::deleteFile()
     */
    public function deleteFile($target_url)
    {
        try {
            $target_path = str_replace($this->option('url'), '', $target_url);
            $save_path = $this->option('root') . $target_path;
            
            // 删除文件
            @unlink($save_path);
            
            return Format::formatResult(1, '删除文件成功');
        } catch (\Exception $e) {
            return Format::formatResult(0, '删除文件发生意外');
        }
    }
}