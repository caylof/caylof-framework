<?php
namespace caylof;

/**
 * 配置信息获取类
 *
 * <pre>
 *   Conf::setFilePath('配置文件路径');
 *   Conf::get();    // 获取所有信息
 *   Conf::get('author');    // 获取配置信息中的“author”的值
 *   Conf::get('mysql.dbname');    // 获取配置信息中“mysql”下的“dbname”的值
 * </pre>
 *
 * @package caylof
 * @author caylof
 */
final class Conf {

    /**
     * 配置信息存储变量
     *
     * @var array
     */
    private static $cfgs;

    /**
     * 配置文件路径存储变量
     *
     * @var string
     */
    private static $filePath;

    /**
     * 加载配置信息
     * 将配置文件中的信息加载到类中
     *
     * @param string $file 配置文件路径
     * @return array
     */
    private static function load() {
        return self::$cfgs ?: self::$cfgs = include self::$filePath;
    }

    /**
     * 设置配置文件路径
     *
     * @param string $filePath 配置文件路径
     */
    public static function setFilePath($filePath) {
        self::$filePath = $filePath;
    }


    /**
     * 获取配置信息
     *
     * @param string $what 配置节点（层级关系可以用“.”格开）
     * @return mixed
     */
    public static function get($what = null) {
        $nodes = $what ? explode('.', $what) : [];

        $ret = self::load();

        while (count($nodes)) {
            $what = array_shift($nodes);

            if (!isset($ret[$what])) {
                return null;
            }
            $ret = $ret[$what];
        }

        return $ret;
    }
}
