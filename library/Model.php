<?php
namespace cms;

use think\Db;

/**
 * Model类
 *
 * @method \think\db\Query field(string $field) 查询字段
 * @method \think\db\Query alias(string $name) 查询别名
 * @method \think\db\Query where(mixed $field, string $op = null, mixed $condition = null) 查询条件
 * @method \think\db\Query join(mixed $join, mixed $condition = null, string $type = 'INNER') JOIN查询
 * @method \think\db\Query union(mixed $union, boolean $all = false) UNION查询
 * @method \think\db\Query limit(mixed $offset, integer $length = null) 查询LIMIT
 * @method \think\db\Query order(mixed $field, string $order = null) 查询ORDER
 * @method \think\db\Query cache(mixed $key = true , integer $expire = null) 设置查询缓存
 * @method mixed value(string $field) 获取某个字段的值
 * @method array column(string $field, string $key = '') 获取某个列的值
 * @method \think\db\Query view(mixed $join, mixed $field = null, mixed $on = null, string $type = 'INNER') 视图查询
 * @method mixed find(mixed $data = []) 查询单个记录
 * @method mixed select(mixed $data = []) 查询多个记录
 * @method integer insert(array $data, boolean $replace = false, boolean $getLastInsID = false, string $sequence = null) 插入一条记录
 * @method integer insertGetId(array $data, boolean $replace = false, string $sequence = null) 插入一条记录并返回自增ID
 * @method integer insertAll(array $dataSet) 插入多条记录
 * @method integer update(array $data) 更新记录
 * @method integer delete(mixed $data = []) 删除记录
 * @method boolean chunk(integer $count, callable $callback, string $column = null) 分块获取数据
 * @method mixed query(string $sql, array $bind = [], boolean $fetch = false, boolean $master = false, mixed $class = false) SQL查询
 * @method integer execute(string $sql, array $bind = [], boolean $fetch = false, boolean $getLastInsID = false, string $sequence = null) SQL执行
 * @method \think\paginator\PaginatorCollection paginate(integer $listRows = 15, boolean $simple = false, array $config = []) 分页查询
 * @method mixed transaction(callable $callback) 执行数据库事务
 * @method string getLastSql() 获取最后一条SQL
 */
abstract class Model
{
    
    use \kucms\traits\Instance;

    /**
     * 数据库连接
     *
     * @var array
     */
    protected static $links = [];

    /**
     * 连接配置
     *
     * @var mixed
     */
    protected $connection = [];

    /**
     * 表名
     *
     * @var string
     */
    protected $table = '';

    /**
     * 去前缀表名
     *
     * @var string
     */
    protected $name = '';

    /**
     * 自动写入时间戳
     *
     * @var boolean
     */
    protected $autoWriteTimestamp = false;

    /**
     * 创建时间字段
     *
     * @var string
     */
    protected $createTime = 'create_time';

    /**
     * 更新时间字段
     *
     * @var string
     */
    protected $updateTime = 'update_time';

    /**
     * 删除时间字段
     *
     * @var string
     */
    protected $deleteTime = 'delete_time';

    /**
     * 数据库对象
     *
     * @param boolean $is_new            
     * @return \think\db\Query
     */
    public function db($is_new = false)
    {
        $class = get_called_class();
        if (! isset(static::$links[$class]) || $is_new) {
            $db = Db::connect($this->connection, $is_new);
            
            if (! empty($this->table)) {
                $db = $db->setTable($this->table);
            } else {
                $db = $db->name($this->name);
            }
            
            if ($is_new) {
                return $db;
            } else {
                static::$links[$class] = $db;
            }
        }
        return static::$links[$class];
    }

    /**
     * 单条记录
     *
     * @param string $value            
     * @param string $field            
     * @param string $name            
     */
    public function get($value, $field = '*', $name = 'id')
    {
        $map = array(
            $name => $value
        );
        return $this->where($map)
            ->field($field)
            ->find();
    }

    /**
     *
     * 添加记录
     *
     * @param mixed $data            
     * @param boolean $flag            
     * @return number
     */
    public function add($data, $return_id = true)
    {
        // 创建时间
        if ($this->autoWriteTimestamp && ! (isset($data[$this->createTime]) && $data[$this->createTime])) {
            $data[$this->createTime] = $data[$this->updateTime] = time();
        }
        
        return $return_id ? $this->insertGetId($data) : $this->insert($data);
    }

    /**
     * 修改记录
     *
     * @param mixed $data            
     * @param mixed $map            
     * @return number
     */
    public function save($data, $map)
    {
        is_array($map) || $map = [
            'id' => $map
        ];
        
        // 修改时间
        if ($this->autoWriteTimestamp && ! (isset($data[$this->updateTime]) && $data[$this->updateTime])) {
            $data[$this->updateTime] = time();
        }
        
        return $this->where($map)->update($data);
    }

    /**
     * 更改记录
     *
     * @param int $id            
     * @param string $field            
     * @param string $value            
     * @return number
     */
    public function modify($id, $field, $value)
    {
        $map = [
            'id' => $id
        ];
        $data = [
            $field => $value
        ];
        return $this->save($data, $map);
    }

    /**
     * 删除记录
     *
     * @param mixed $map            
     * @param boolean $is_logic            
     * @return number
     */
    public function del($map, $is_logic = false)
    {
        is_array($map) || $map = [
            'id' => $map
        ];
        
        if ($is_logic == false) {
            // 物理删除
            return $this->where($map)->delete();
        } else {
            // 逻辑删除
            $data = array(
                $this->deleteTime => time()
            );
            return $this->save($data, $map);
        }
    }

    /**
     * 逻辑恢复
     *
     * @param mixed $map            
     * @return number
     */
    public function recover($map)
    {
        $data = array(
            $this->deleteTime => 0
        );
        return $this->save($data, $map);
    }

    /**
     * name，用于join等操作
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * table，用于join等操作
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * 调用think\db\Query的方法
     *
     * @param unknown $method            
     * @param unknown $params            
     * @return mixed
     */
    public function __call($method, $params)
    {
        return call_user_func_array([
            $this->db(),
            $method
        ], $params);
    }
}