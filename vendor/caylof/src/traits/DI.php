<?php
namespace caylof\traits;

trait DI {

    /**
     * 依赖容器
     *
     * @var \Caylof\Container $di
     */
    private $di;

    /**
     * 设置依赖容器
     *
     * @param \Caylof\Container $di
     */
    public function setDi(\Caylof\Container $di) {
        $this->di = $di;
    }

    /**
     * 获取依赖容器
     *
     * @return \Caylof\Container
     */
    public function getDi() {
        return $this->di;
    }
}
