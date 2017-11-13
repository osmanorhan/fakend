<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \FakendFactory;
use League\Fractal\Serializer\JsonApiSerializer;

$app = new Silex\Application();
$app['debug'] = true;

$app->match('/posts', function(Request $request) use ($app) {
    if($request->getMethod() == 'OPTIONS') {
        return new Response('', 200);
    }
    $post = FakendFactory::create('post');
    $post->setSerializer(new JsonApiSerializer());
    $return = $post->setMeta(array('totalCount' => 11))->getMany(5);
    return new Response($return, 200, array(
        'Content-Type' => 'application/json',
    ));
})->method('GET|OPTIONS');
/*
$app->match('/users/{id}', function($id, Request $request) use ($app) {
    if($request->getMethod() == 'OPTIONS') {
        return new Response('', 200);
    }
    $post = FakendFactory::create('post');
    $post->setSerializer(new JsonApiSerializer());
    $return = $post->get($id);
    return new Response($return, 200, array(
        'Content-Type' => 'application/json',
    ));
})->method('GET|OPTIONS');
$app->match('/users/{id}', function($id, Request $request) use ($app) {
    if($request->getMethod() == 'OPTIONS') {
        return new Response('', 200);
    }
    $return = json_encode(array());
    return new Response($return, 200, array(
        'Content-Type' => 'application/json',
    ));
})->method('DELETE|OPTIONS');
*/
$app->run();