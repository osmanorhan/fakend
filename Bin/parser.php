#!/usr/bin/env php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/EmberDataParser.php';
use Fakend\Bin\EmberDataParser;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new EmberDataParser());
$application->run();