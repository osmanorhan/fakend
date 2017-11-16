<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Fakend\FakendFactory;
use League\Fractal\Serializer\JsonApiSerializer;
$app = new Silex\Application();
$app['debug'] = true;

$app->run();