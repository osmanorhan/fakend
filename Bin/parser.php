#!/usr/bin/env php
<?php
require_once __DIR__ . '/../../../autoload.php';
require_once __DIR__ . '/DataParser.php';
use Fakend\Bin\DataParser;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new DataParser());
$application->run();