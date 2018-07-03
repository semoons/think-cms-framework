<?php
namespace cms;

/**
 * Logic类
 *
 * @property \cms\Model $model
 * @property \cms\Validate $validate
 */
abstract class Logic
{
    use \kucms\traits\Instance;

    /**
     * 模型类名
     *
     * @var unknown
     */
    protected $modelClass;

    /**
     * 验证类名
     *
     * @var unknown
     */
    protected $validateClass;

    /**
     * ModelClass
     *
     * @return string
     */
    private function getModelClass()
    {
        $class = get_called_class();
        return str_replace([
            'logic',
            'Logic'
        ], [
            'model',
            'Model'
        ], $class);
    }

    /**
     * ValidateClass
     *
     * @return string
     */
    private function getValidateClass()
    {
        $class = get_called_class();
        return str_replace([
            'logic',
            'Logic'
        ], [
            'validate',
            'Validate'
        ], $class);
    }

    /**
     * 模型对象
     *
     * @return \cms\Model
     */
    public static function model()
    {
        return static::instance()->model;
    }

    /**
     * 验证对象
     *
     * @return \cms\Validate
     */
    public static function validate()
    {
        return static::instance()->validate;
    }

    /**
     * 获取model和validate对象
     *
     * @param string $name            
     * @return mixed
     */
    public function __get($name)
    {
        $name = strtolower($name);
        if ($name == 'model') {
            $model_class = $this->modelClass ?  : $this->getModelClass();
            return $model_class::instance();
        } elseif ($name == 'validate') {
            $validate_class = $this->validateClass ?  : $this->getValidateClass();
            return $validate_class::instance();
        }
    }
}