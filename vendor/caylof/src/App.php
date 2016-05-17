<?php
namespace caylof;

/**
 * 全局服务容器类
 *
 * @package caylof
 * @author caylof
 */
final class App extends Container {

    use Traits\Singleton;

    private $registry = [];
}
