<?php

require __DIR__.'/boots.test.php';

use caylof\Router;


class ArticleCtrl {
    public function show($articleId, $commentId) {
        return sprintf('articleId: %d, commentId: %d', $articleId, $commentId);
    }
}

$router = Router::getInstance();


$router->get('/', function() {
    return 'Hello';
});

$router->get(
    '/cat/:what', //点位符用“:xxx”表示
    'CategoryCtrl@show',
    '[\x{4e00}-\x{9fa5}]+' // 匹配中文
)->get(
    '/article/:id/comment/:cid',
    'ArticleCtrl@show',
    //['id'=>'\d+', 'cid'=>'\d+']
    '\d+'
);


$ret1 = $router->find('/cat/编程', 'GET');
$ret2 = $router->find('/article/1/comment/2', 'GET');

print_var($ret1);
print_var($ret2);

//$r = $router->find('/', 'GET');
//$r = $router->find('/article/11/comment/2', 'GET');
//print_var($router->dispatch($r['router']['todo'], $r['params']));
