<?php
namespace cms\upload\driver;

use cms\Upload;
use Upyun\Config;
use Upyun\Upyun;
use Upyun\Api\Multi;
use GuzzleHttp\Psr7;
use newday\common\Format;

class UpyunUpload extends Upload
{

    /**
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \app\common\driver\Upload::uploadFile()
     */
    public function uploadFile($path, $target_path)
    {
        // 允许客户端断开连接
        ignore_user_abort(true);
        
        try {
            // 文件大于设定值进行分片上传
            $file_size = filesize($path);
            $save_path = $this->option('root') . $target_path;
            if ($file_size > $this->option('maxsize') * 1048576) {
                $data = array(
                    'return_url' => $this->option('return'),
                    'notify_url' => $this->option('notify')
                );
                $stream = Psr7\stream_for(fopen($path, 'rb'));
                $rsp = $this->getUpYunChunk()->upload($save_path, $stream, md5_file($path), $data);
            } else {
                $fh = fopen($path, 'r');
                $rsp = $this->getUpYun()->write($save_path, $fh);
            }
            
            // 拼接链接
            $url = $this->option('url') . $target_path;
            return Format::formatResult(1, '上传成功', $url);
        } catch (\Exception $e) {
            return Format::formatResult(0, '上传文件发生意外:' . $e->getLine() . $e->getFile() . $e->getMessage());
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
            $content = $this->getUpYun()->read($save_path);
            
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
            $this->getUpYun()->delete($save_path);
            
            return Format::formatResult(1, '删除文件成功');
        } catch (\Exception $e) {
            return Format::formatResult(0, '删除文件发生意外');
        }
    }

    /**
     * 普通上传对象
     *
     * @return \UpYun
     */
    public function getUpYun()
    {
        if (empty($this->upyun)) {
            $this->upyun = new UpYun($this->getUpyunConfig());
        }
        return $this->upyun;
    }

    /**
     * 分片上传对象
     *
     * @return \Upyun\Api\Multi
     */
    public function getUpYunChunk()
    {
        if (empty($this->upyun_chunk)) {
            $this->upyun_chunk = new Multi($this->getUpyunConfig());
        }
        return $this->upyun_chunk;
    }

    /**
     * 获取又拍云配置
     *
     * @return \Upyun\Config
     */
    public function getUpyunConfig()
    {
        if (empty($this->upyun_config)) {
            $this->upyun_config = new Config($this->option('bucket'), $this->option('user'), $this->option('passwd'));
            
            // 表单key
            $this->upyun_config->setFormApiKey($this->option('key'));
            
            // 分片大小
            $this->upyun_config->maxBlockSize = $this->option('size') * 1048576;
            
            // 单片超时时间
            $this->upyun_config->timeout = $this->option('timeout');
        }
        return $this->upyun_config;
    }
}