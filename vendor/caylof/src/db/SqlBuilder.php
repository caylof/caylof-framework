<?php
namespace caylof\db;

/**
 * 数据库操作类
 *
 * @package caylof
 * @author caylof
 */
class SqlBuilder {

    /**
     * 对应表中字段集
     *
     * @var array
     */
    private $fields = [];

    /**
     * 对应数据库中的表
     *
     * @var array
     */
    private $tables = [];

    /**
     * where条件
     *
     * @var array
     */
    private $cases  = [];

    /**
     * 用于缓存拼装过程中的sql语句占位符的参数集
     *
     * @var array
     */
    private $params = [];

    /**
     * join语句
     *
     * @var array
     */
    private $join   = [];

    /**
     * 排序条件
     *
     * @var string
     */
    private $order  = [];

    /**
     * 分组条件
     *
     * @var string
     */
    private $group  = [];

    /**
     * limit条件
     *
     * @var string
     */
    private $limit;

    /**
     * sql语句类型
     * 四种类型：SELECT、UPDATE、INSERT、DELETE
     *
     * @var string
     */
    private $sqlType;

    /**
     * 存储拼装完成后的sql语句
     *
     * @var string
     */
    private $sql;

    /**
     * 存储拼装完成后的sql语句中的参数占位符
     *
     * @var array
     */
    private $sqlParams;

    /**
     * 组装带有别名的sql语句
     *
     * @param array $arr 要组装的数据(格式：['字段名',]或者['别名' => '字段名',])
     * @param string $sp 分隔符
     * @return string
     */
    private function combineSql(array $arr, $sp = ',') {
        $sql = [];
        foreach ($arr as $alias => $value) {
            $sql[] = is_numeric($alias) ? $value : join(' AS ', [$value, $alias]);
        }
        return join($sp, $sql);
    }

    /**
     * 创建sql语句
     *
     * @return string
     */
    private function createSql() {
        $fieldSql = $this->combineSql($this->fields);
        $tableSql = $this->combineSql($this->tables);
        $caseSql  = join(' AND ', $this->cases);
        !$caseSql?: $caseSql = sprintf(' WHERE %s', $caseSql);

        switch ($this->sqlType) {
            case 'SELECT':
                $sql = sprintf('SELECT %s FROM %s', $fieldSql, $tableSql);
                !$this->join  ?: $sql .= sprintf(' %s', join(' ', $this->join));
                $sql .= $caseSql;
                !$this->order ?: $sql .= sprintf(' ORDER BY %s', join(',', $this->order));
                !$this->group ?: $sql .= sprintf(' GROUP BY %s', join(',', $this->group));
                !$this->limit ?: $sql .= sprintf(' %s', $this->limit);
                break;
            case 'UPDATE':
                $sql = sprintf("UPDATE %s SET %s%s", $tableSql, $fieldSql, $caseSql);
                break;
            case 'INSERT':
                $values = join(',', array_pad([], count($this->fields), '?'));
                $sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", $tableSql, $fieldSql, $values);
                break;
            case 'DELETE':
                $sql = sprintf("DELETE FROM %s%s", $tableSql, $caseSql);
                break;
            default:
                // do nothing
        }

        return $sql;
    }

    /**
     * 释放缓存数据，用于sql语句创建完成之后
     */
    private function free() {
        $this->fields = [];
        $this->tables = [];
        $this->cases  = [];
        $this->params = [];
        $this->join   = [];
        $this->order  = [];
        $this->group  = [];

        $this->limit   = null;
        $this->sqlType = null;
    }

    /**
     * select语句
     *
     * <pre>
     *   // 查询所有字段
     *   $curd->select();
     *
     *   // 查询一个字段
     *   $curd->select('username');
     *
     *   // 查询多个字段
     *   $curd->select(['username', 'passwd']);
     *
     *   // 字段别名（数组中的key值为别名，value值为字段名）
     *   $curd->select(['name' => 'username', 'pwd' => 'passwd']);
     * </pre>
     *
     * @param scalar|array $fields 查询字段，默认为所有字段
     * @return $this
     */
    public function select($fields = ['*']) {
        $this->sqlType = 'SELECT';
        is_array($fields) ?: $fields = [$fields];
        $this->fields = array_merge($this->fields, $fields);

        return $this;
    }

    /**
     * update语句
     *
     * @param array $set 要修改的数据，格式：['字段名' => '修改后的值', ...]
     * @return $this
     */
    public function update(array $set) {
        $this->sqlType = 'UPDATE';
        $this->fields = array_map(function ($v) {
            return "$v=?";
        }, array_keys($set));
        $this->params = array_values($set);

        return $this;
    }

     /**
     * insert语句
     *
     * @param array $values 要修改的数据，格式：['字段名' => '要插入的值', ...]
     * @return $this
     */
    public function insert(array $values) {
        $this->sqlType = 'INSERT';
        $this->fields = array_keys($values);
        $this->params = array_values($values);

        return $this;
    }

    /**
     * delete语句
     *
     * @return $this
     */
    public function delete() {
        $this->sqlType = 'DELETE';

        return $this;
    }

    /**
     * 设置数据表
     *
     * <pre>
     *   // 一个表
     *   $curd->table('user');
     *
     *   // 多个表
     *   $curd->table(['user', 'role']);
     *
     *   // 表别名（数组中的key值为别名，value值为字段名）
     *   $curd->table(['u' => 'user', 'r' => 'role']);
     *
     *   // 结合select语句
     *   // sql语句相当于【SELECT u.username AS name FROM user AS u】
     *   $curd->select(['name' => 'u.username'])->table(['u' => 'user']);
     * </pre>
     *
     * @param scalar|array $tables 数据表
     * @return $this
     */
    public function table($tables) {
        is_array($tables) ?: $tables = [$tables];
        $this->tables = array_merge($this->tables, $tables);

        return $this;
    }

    /**
     * 设置where条件语句
     * 多个where方法之间的关系是AND关系
     *
     * <pre>
     *   // ...WHERE id=3
     *   $curd->where(['id' => 3]);
     *
     *   // ...WHERE username='cctv' AND role_id=1
     *   $curd->where(['username' => 'cctv', 'role_id' => 1]);
     *
     *   // 多个where还可以链式写，它们之间的关系相当于AND
     *   $curd->where(['username' => 'cctv'])
     *        ->where(['role_id' => 1]);
     *
     *   // 结合别名的使用
     *   // SQL【SELECT u.username AS u_name, r.name AS r_name FROM user AS u, role AS r WHERE u_name='cctv' AND r.role_id=1】
     *   $curd->select(['u_name' => 'u.username', 'r_name' => 'r.name'])
     *        ->table(['u' => 'user', 'r' => 'role'])
     *        ->where(['u_name' => 'cctv', 'r.role_id' => 1]);
     * </pre>
     *
     * @param array $cases 条件数组
     * @return $this
     */
    public function where(array $cases) {
        $this->cases = array_merge($this->cases, array_map(function ($v) {
            return "$v=?";
        }, array_keys($cases)));
        $this->params = array_merge($this->params, array_values($cases));

        return $this;
    }

    /**
     * 直接设置where语句
     * 用于解决where方法中构造复杂条件语句的不足
     *
     * <pre>
     *   $curd->whereRaw('id>1');
     *   $curd->whereRaw('id>?', 2);
     *   $curd->whereRaw('id>? OR role_id=?', [2, 1]);
     * </pre>
     *
     * @param string $whereCase where条件语句
     * @param scalar|array 条件语句中占位符的参数集
     * @return $this;
     */
    public function whereRaw($whereCase, $params = []) {
        is_array($params) ?: $params = [$params];
        $this->params = array_merge($this->params, $params);
        $this->cases[] = $whereCase;

        return $this;
    }

    /**
     * 设置join条件
     *
     * <pre>
     *   $curd->select()
     *        ->table('user')
     *        ->join('inner', 'role', 'user.role_id = role.id')
     *        ->build();
     *
     *   $curd->select()
     *        ->table(['u' => 'user'])
     *        ->join('inner', ['r' => 'role'], 'u.role_id = r.id')
     *        ->build();
     * </pre>
     *
     * @param string $type join类型【inner, left, right, full】
     * @param string|array $table join表
     * @param string  $on join条件
     * @return $this
     */
    public function join($type, $table, $on) {
        is_array($table) ?: $table = [$table];
        $table = is_numeric(key($table)) ? current($table) : join(' AS ', [current($table), key($table)]);
        $this->join[] = join(' ', [strtoupper($type), 'JOIN', $table, 'ON', $on]);
        return $this;
    }

    /**
     * order by语句
     *
     * <pre>
     *   $curd->select()
     *        ->table('user')
     *        ->orderBy('id DESC')
     *        ->orderBy('name DESC')
     *        ->build();
     *
     *   $curd->select()
     *        ->table('user')
     *        ->orderBy(['id DESC', 'name DESC'])
     *        ->build();
     * </pre>
     *
     * @param array $order 排序条件
     * @return $this
     */
    public function orderBy($order = []) {
        is_array($order) ?: $order = [$order];
        $this->order[] = join(',', $order);
        return $this;
    }

    /**
     * group by语句
     *
     * <pre>
     *   $curd->select()
     *        ->table('user')
     *        ->groupBy('name')
     *        ->build();
     *
     *   $curd->select()
     *        ->table('user')
     *        ->groupBy(['name', 'age'])
     *        ->build();
     * </pre>
     *
     * @param array $group 分组条件
     * @return $this
     */
    public function groupBy($group = []) {
        is_array($group) ?: $group = [$group];
        $this->group[] = join(',', $group);
        return $this;
    }

    /**
     * limit语句
     *
     * <pre>
     *   $curd->select()
     *        ->table('user')
     *        ->limit(0, 10)
     *        ->build();
     * </pre>
     *
     * @param int $start 限制起始位置
     * @param int $count 限制数目
     * @return $this
     */
    public function limit($start, $count) {
        $this->limit = sprintf('LIMIT %d, %d', $start, $count);
        return $this;
    }


    /**
     * 调用该方法开始组建sql，完成后清空缓存数据
     *
     * @return $this
     */
    public function build() {
        $this->sql = $this->createSql();
        $this->sqlParams = $this->params;
        $this->free();
        return $this;
    }

    /**
     * 供PDO执行sql语句
     *
     * @param \Caylof\Db\PDO $pdo
     * @return PDOStatement|boolean
     */
    public function runBy(PDO $pdo) {
        return $pdo->query($this->getSql(), $this->getSqlParams());
    }

    /**
     * 获取sql语句
     *
     * @return string
     */
    public function getSql() {
        return $this->sql;
    }

    /**
     * 获取sql语句中的点位符参数
     *
     * @return array
     */
    public function getSqlParams() {
        return $this->sqlParams;
    }
}
