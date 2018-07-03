<?php
namespace cms;

use cms\Common;
use think\Image;
use kucms\http\Http;
use kucms\common\Format;
use kucms\file\FileInfo;

abstract class Upload
{
    
    use \kucms\traits\Instance;

    /**
     * 上传类型
     *
     * @var unknown
     */
    protected $upload_type = [
        'image' => [
            'jpg',
            'png',
            'gif',
            'bmp'
        ],
        'audio' => [
            'mp3',
            'ogg',
            'm4a',
            'wav',
            'ape',
            'flac'
        ],
        'video' => [
            'mp4',
            'mov',
            'mpg',
            'flv',
            'mkv',
            'avi'
        ],
        'compress' => [
            'zip',
            'rar',
            '7z'
        ],
        'document' => [
            'doc',
            'xls',
            'ppt',
            'docx',
            'xlsx',
            'pptx',
            'pdf'
        ]
    ];

    /**
     * 是否上传过
     *
     * @var unknown
     */
    public $onCheck;

    /**
     * 上传完成
     *
     * @var unknown
     */
    public $onDone;

    /**
     * 上传失败
     *
     * @var unknown
     */
    public $onError;

    /**
     * 上传成功
     *
     * @var unknown
     */
    public $onSuccess;

    /**
     * 本地
     *
     * @var unknown
     */
    const TYPE_LOCAL = 'local';

    /**
     * 又拍云
     *
     * @var unknown
     */
    const TYPE_UPYUN = 'upyun';

    /**
     * 创建上传对象
     *
     * @param array $option            
     * @param string $type            
     * @return self
     */
    public static function create($option = [], $type = self::TYPE_LOCAL)
    {
        $class_name = '\\cms\\upload\\driver\\' . ucfirst($type) . 'Upload';
        if (class_exists($class_name)) {
            try {
                return $class_name::instance($option);
            } catch (\Exception $e) {}
        }
    }

    /**
     * 上传文件
     *
     * @param mixed $path            
     * @param string $type            
     * @param array $option            
     * @return array
     */
    public function upload($path, $type = '', $option = [])
    {
        if (is_array($path)) {
            return $this->uploadByFile($path, $type, $option);
        } elseif (is_file($path)) {
            return $this->uploadByPath($path, $type, $option);
        } else {
            return $this->uploadByUrl($path, $type, $option);
        }
    }

    /**
     * 根据URL上传
     *
     * @param string $url            
     * @param string $type            
     * @param array $option            
     * @return array
     */
    public function uploadByUrl($url, $type = '', $option = [])
    {
        // 抓取远程文件
        $content = Http::instance()->request($url);
        if (empty($content)) {
            return Format::formatResult(0, '读取远程文件失败');
        }
        
        return $this->uploadByContent($content, $type = '', $option = []);
    }

    /**
     * 根据Content上传
     *
     * @param string $content            
     * @param string $type            
     * @param array $option            
     * @return array
     */
    public function uploadByContent($content, $type = '', $option = [])
    {
        // 临时文件
        $tmp_file = Common::tmpFile('upload');
        file_put_contents($tmp_file, $content);
        
        // 上传完删除文件
        $this->onDone = function ($data) {
            @unlink($data['path']);
        };
        
        return $this->uploadByPath($tmp_file, $type, $option);
    }

    /**
     * 根据FILE上传
     *
     * @param array $file            
     * @param string $type            
     * @param array $option            
     * @return array
     */
    public function uploadByFile($file, $type = '', $option = [])
    {
        if ($file['error'] > 0) {
            switch ($file['error']) {
                case 1:
                    $info = '文件超过服务器允许上传的大小';
                    break;
                case 2:
                    $info = '文件超过表单允许上传的大小';
                    break;
                case 3:
                    $info = '文件只有部分被上传';
                    break;
                case 4:
                    $info = '没有找到要上传的文件';
                    break;
                case 5:
                    $info = '服务器临时文件夹丢失';
                    break;
                case 6:
                    $info = '没有找到临时文件夹';
                    break;
                case 7:
                    $info = '写入临时文件失败';
                    break;
                case 8:
                    $info = 'PHP不允许上传文件';
                    break;
                default:
                    $info = '未知的上传错误';
                    break;
            }
            return Format::formatResult(0, $info);
        } else {
            $option['mime'] = $file['type'];
            return self::uploadByPath($file['tmp_name'], $type, $option);
        }
    }

    /**
     * 根据路径上传
     *
     * @param string $path            
     * @param string $type            
     * @param array $option            
     * @return array
     */
    public function uploadByPath($path, $type = '', $option = [])
    {
        // 文件是否存在
        if (empty($path) || ! is_file($path)) {
            return Format::formatResult(0, '文件不存在');
        }
        
        // 没有指定后缀
        if (! isset($option['ext'])) {
            $option['ext'] = FileInfo::getFileExt($path);
        }
        
        // 类型识别出错
        if (empty($option['ext']) && isset($option['mime'])) {
            $option['ext'] = FileInfo::mimeToExt($option['mime']);
        }
        
        // 上传文件类型
        $type = $this->getFileType($option['ext'], $type);
        if (empty($type)) {
            return Format::formatResult(0, '不合法的文件类型');
        }
        
        // 处理图片
        $type == 'image' && $this->processImage($path, $option);
        
        // 没有文件Md5
        if (! isset($option['hash'])) {
            $option['hash'] = md5_file($path);
        }
        
        // 是否上传过
        if ($this->onCheck && is_callable($this->onCheck)) {
            $url = call_user_func($this->onCheck, [
                'hash' => $option['hash'],
                'type' => $type,
                'ext' => $option['ext'],
                'path' => $path
            ]);
            
            // 文件存在
            if (! empty($url)) {
                return Format::formatResult(1, '上传成功', [
                    'url' => $url,
                    'hash' => $option['hash'],
                    'type' => $type,
                    'ext' => $option['ext'],
                    'path' => $this->option('root') . DS . $type . DS . $option['hash'] . '.' . $option['ext']
                ]);
            }
        }
        
        // 上传文件
        $target_path = $type . '/' . $option['hash'] . '.' . $option['ext'];
        $res = $this->uploadFile($path, $target_path);
        
        // 上传完成
        $this->onDone && is_callable($this->onDone) && call_user_func($this->onDone, [
            'hash' => $option['hash'],
            'type' => $type,
            'ext' => $option['ext'],
            'path' => $path
        ]);
        
        // 上传失败
        if ($res['code'] != 1) {
            $this->onError && is_callable($this->onError) && call_user_func($this->onError, [
                'hash' => $option['hash'],
                'type' => $type,
                'ext' => $option['ext'],
                'path' => $path
            ]);
            return $res;
        }
        
        // 上传成功
        $this->onSuccess && is_callable($this->onSuccess) && call_user_func($this->onSuccess, [
            'hash' => $option['hash'],
            'type' => $type,
            'ext' => $option['ext'],
            'path' => $path,
            'url' => $res['data']
        ]);
        
        // 返回结果
        return Format::formatResult(1, '上传成功', [
            'url' => $res['data'],
            'hash' => $option['hash'],
            'type' => $type,
            'ext' => $option['ext'],
            'path' => $this->option('root') . DS . $type . DS . $option['hash'] . '.' . $option['ext']
        ]);
    }

    /**
     * 处理图片
     *
     * @param string $path            
     * @param string $option            
     * @return array
     */
    public function processImage($path, $option = [])
    {
        // 图片重力
        try {
            if (extension_loaded('exif')) {
                $image = imagecreatefromstring(Common::readFile($path));
                $exif = exif_read_data($path);
                if (! empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 8:
                            $image = imagerotate($image, 90, 0);
                            break;
                        case 3:
                            $image = imagerotate($image, 180, 0);
                            break;
                        case 6:
                            $image = imagerotate($image, - 90, 0);
                            break;
                    }
                    imagejpeg($image, $path);
                }
            }
        } catch (\Exception $e) {}
        
        // 图片大小
        try {
            if (extension_loaded('gd')) {
                if (isset($option['width']) || isset($option['height'])) {
                    $image = Image::open($path);
                    if ($image) {
                        $width = isset($option['width']) && ! empty($option['width']) ? $option['width'] : $image->width();
                        $height = isset($option['height']) && ! empty($option['height']) ? $option['height'] : $image->height();
                        $image = $image->thumb($width, $height);
                        $image->save($path);
                    }
                }
            }
        } catch (\Exception $e) {}
    }

    /**
     * 上传类型
     *
     * @return array
     */
    public function getUploadType()
    {
        $upload_type = $this->option('upload_type');
        $upload_type || $upload_type = $this->upload_type;
        return $upload_type;
    }

    /**
     * 文件类型
     *
     * @param string $ext            
     * @param string $type            
     * @return array
     */
    public function getFileType($ext, $type = '')
    {
        // 可上传类型
        $upload_type = $this->getUploadType();
        
        // 没有指定上传类型
        if ($type == '') {
            foreach ($upload_type as $c => $v) {
                if (in_array($ext, $v)) {
                    $type = $c;
                    break;
                }
            }
        }
        
        // 验证上传类型
        if ($type == '' || ! isset($upload_type[$type]) || ! in_array($ext, $upload_type[$type])) {
            return '';
        } else {
            return $type;
        }
    }

    /**
     * 上传文件
     *
     * @param string $path            
     * @param string $target_path            
     * @return array
     */
    abstract public function uploadFile($path, $target_path);

    /**
     * 读取文件
     *
     * @param string $target_url            
     * @return array
     */
    abstract public function fetchFile($target_url);

    /**
     * 删除文件
     *
     * @param unknown $target_url            
     * @return array
     */
    abstract public function deleteFile($target_url);
}