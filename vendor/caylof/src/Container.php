<?php
namespace caylof;

/**
 * 服务容器类
 *
 * @package caylof
 * @author caylof
 */
class Container {

    private $registry = [];

    /**
     * 添加服务到容器
     *
     * @param string $name 服务名称
     * @param Closure $resolver 服务对象闭包
     */
    public function set($name, \Closure $resolver) {
        $this->registry[$name] = $resolver;
    }

    /**
     * 添加(唯一的)服务到容器
     *
     * @param string $name 服务名称
     * @param Closure $resolver 服务对象闭包
     */
    public function setSingleton($name, \Closure $resolver) {
        $this->registry[$name] = function() use ($resolver) {
            static $res;
            return $res ?: $res = $resolver();
        };
    }

    /**
     * 获取服务
     *
     * @param string $name 服务名称
     * @return mixed 服务对象
     * @throw Exception 服务不存在时抛出例外
     */
    public function get($name) {
        if (isset($this->registry[$name])) {
            $resolver = $this->registry[$name];
            return $resolver();
        }
        throw new \Exception(sprintf('"%s" does not exist in the "%s"', $name, __CLASS__));
    }

    /**
     * 获取共享的服务
     * 如果某个服务在注册时不是以唯一的方式(setSingleton)时，而却想获取该共享服务时可用此方法
     *
     * @param string $name 服务名称
     * @return mixed 服务对象
     */
    public function getShared($name) {
        static $share = [];
        return isset($share[$name]) ? $share[$name] : $share[$name] = $this->get($name);
    }

    public function __set($name, \Closure $resolver) {
        $this->set($name, $resolver);
    }

    public function __get($name) {
        return $this->get($name);
    }

    /**
     * 注册服务（以配置文件的方式）
     * 配置文件返回一个数组
     *
     * 单个服务格式为：['name'=>'名称', 'provider'=>'类名', 'singleton'=>false, 'params'=>[...]]
     * 其中'name'和'provider'是必须的
     * 如果要使注册的服务唯一，应该加上【'singleton'=>true】，否则该项默认为false(表示不唯一)
     * 如果服务类provider的构造函数中有参数，应该加上【'params'=>['参数1',...]】，参数必须为数组
     * 
     * <pre>
     *   $services = [
     *       ['name' => 'pdo', 'singleton' => true, 'provider' => '\Caylof\Db\PDO', 'params' => [$dsn, $username, $password]],
     *       ['name' => 'sqlBuilder', 'singleton' => false, 'provider' => '\Caylof\Db\SqlBuilder']
     *   ];
     * </pre>
     *
     * @param array $services 服务组件
     */
    public function register(array $services) {
        foreach ($services as $service) {

            $closure = function() use ($service) {
                $rc = new \ReflectionClass($service['provider']);
                if (isset($service['params'])) {
                    return $rc->newInstanceArgs($service['params']);
                }
                return $rc->newInstance();
            };

            if (isset($service['singleton']) && !!$service['singleton']) {
                $this->setSingleton($service['name'], $closure);
            } else {
                $this->set($service['name'], $closure);
            }
        }
    }
}
