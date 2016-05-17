<?php
namespace caylof\mvc;

class Controller {

    /**
     * url跳转
     *
     * <pre>
     *  redirect('test/user/list');
     *  redirect(array('test', 'user', 'list'));
     * </pre>
     *
     * @param string|array $url
     * @param bool $query 是否带上跳转前的路径的query参数
     */
    public function redirect($url, $query = false) {
        if (is_array($url)) {
            $url = array_map('trim', $url);
            $url = join('/', $url);
        }
        $url = '/'.trim($url, '/');
        $url .= $query && $_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '';
        header('Location: ' . $url);
    }

    public function render($viewPath, array $data) {
        
    }
}
