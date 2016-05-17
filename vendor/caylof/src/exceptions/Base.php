<?php
namespace caylof\exceptions;

/**
 * 例外处理类
 *
 * @package caylof
 * @author caylof
 */
class Base extends \Exception {

    /**
     * 构造函数
     *
     * @param string $message 异常消息内容
     * @param int code 异常代码
     */
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }
}
