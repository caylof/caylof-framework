<?php
namespace caylof;

/**
 * 路由请求类
 *
 * @package caylof
 * @author caylof
 */
class Router {

    use traits\Singleton;

    /**
     * 路由存储列表
     *
     * @access private
     * @var array
     */
    private $routerList;

    /**
     * 添加路由请求
     *
     * @param string $type 请求类型（“GET”, “POST”）
     * @param string $route 路由路径
     * @param string|\Closure $todo 形式如“controller@method”的字符串或者“闭包”
     * @rule string|array $rule 路由路径中点位符规则
     */
    public function add($type, $route, $todo, $rule = '\w+') {
        $route = '/'.trim($route, '/ ');
        $this->checkRouteTodo($todo);

        $patternDefault = '#:\w+#';
        $repalceDefault = '(\w+)';

        $pattern = [];
        $replace = [];
        if (is_array($rule)) {
            foreach ($rule as $placeholder => $value) {
                $pattern[] = '#:'.$placeholder.'#';
                $replace[] = '('.$value.')';
            }
        } else {
            $pattern[] = $patternDefault;
            $replace[] = '(' . $rule . ')';
        }

        $reg = preg_replace($pattern, $replace, $route);
        $reg = '#^'.preg_replace($patternDefault, $repalceDefault, $reg).'$#us';

        $this->routerList[$type][md5($reg)] = [
            'route'  => $route,
            'reg'    => $reg,
            'todo'   => $todo
        ];
        return $this;
    }

    /**
     * 添加“GET”类型的请求
     */
    public function get($route, $todo, $rule = '\w+') {
        return $this->add('GET', $route, $todo, $rule);
    }

    /**
     * 添加“POST”类型的请求
     */
    public function post($route, $todo, $rule = '\w+') {
        return $this->add('POST', $route, $todo, $rule);
    }

    public function all($route, $todo, $rule = '\w+') {
        $this->add('GET', $route, $todo, $rule);
        return $this->add('POST', $route, $todo, $rule);
    }

    /**
     * 将“请求”分解到相应“路由”
     */
    public function route() {
        $uri    = parse_url($_SERVER['REQUEST_URI']);
        $uri    = '/'.trim($uri['path'], '/ ');
        $uri    = urldecode($uri);
        $type   = strtoupper($_SERVER['REQUEST_METHOD']);

        $ret    = $this->find($uri, $type);
        $router = $ret['router'];
        $params = $ret['params'];
        $todo   = $router['todo'];

        return $this->dispatch($todo, $params);
    }

    /**
     * 根据“请求”查找相应“路由”
     *
     * @param string $uri 请求uri
     * @param string $type 请求类型（“GET”或者“POST”）
     * @return array('router' => '路由', 'params' => '请求参数')
     */
    public function find($uri, $type) {
        foreach ($this->routerList[$type] as $router) {
            if (preg_match($router['reg'], $uri, $matches)) {
                return [
                    'router' => $router,
                    'params' => array_slice($matches, 1)
                ];
            }
        }
        throw new exceptions\NotFound(sprintf('Router not found by the "%s" request "%s"', $type, $uri));
    }

    /**
     * 调用“路由处理方法”
     *
     * @param string|\Closure $todo 形式如“controller@method”的字符串或者“闭包”
     * @param array $params 方法$todo所需的参数
     */
    public function dispatch($todo, array $params) {
        if ($todo instanceof \Closure) {
            $res = call_user_func_array($todo, $params);
        } else {
            $splits     = explode('@', $todo);
            $className  = array_shift($splits);
            $methodName = array_shift($splits);

            if (!class_exists($className)) {
                throw new exceptions\NotFound(sprintf('class "%s" not exist', $className));
            }

            $rc = new \ReflectionClass($className);

            if (!$rc->hasMethod($methodName )) {
                throw new exceptions\NotFound(sprintf('method "%s" not exist in the class "%s"', $methodName, $className));
            }

            $contro = $rc->newInstance();
            $method = $rc->getMethod($methodName);

            if (count($params)) {
                $res = $method->invokeArgs($contro, $params);
            } else {
                $res = $method->invoke($contro);
            }
        }
        return $res;
    }

    /**
     * 判断“路由处理方法”格式是否正确
     * 字符串中包含“@”则简单认为格式正确
     */
    private function checkRouteTodo($todo) {
        if (is_string($todo) && (strpos($todo, '@') === false)) {
            throw new exceptions\UnexpectedValue('"'.$todo.'"'." expect have \'@\' to split controller and method, but not have");
        }
    }
}
