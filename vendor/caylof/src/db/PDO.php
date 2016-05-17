<?php
namespace caylof\db;

class PDO extends \PDO {

    public function __construct($dsn, $username = null, $password = null, $options = null) {
        parent::__construct($dsn, $username, $password, $options);
        $this->exec('set names utf8');
        $this->setAttribute(self::ATTR_DEFAULT_FETCH_MODE, self::FETCH_ASSOC);
        $this->setAttribute(self::ATTR_ERRMODE, self::ERRMODE_EXCEPTION);
    }

    /**
     * 执行SQL语句
     *
     * @param string $sql
     * @param array $param
     * @return \PDOStatement|boolean
     */
    public function query($sql, array $params = null) {
        $stmt = $this->prepare($sql);
        $bool = $stmt->execute($params);
        return $bool ? $stmt : $bool;
    }

    public function queryWith(SqlBuilder $qb) {
        return $qb->runBy($this);
    }
}
