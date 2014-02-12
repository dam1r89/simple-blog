#!/usr/bin/php -q
<?php

define('CONFIG_FILE', 'simpleblog.yml');

date_default_timezone_set('UTC');

require 'vendor/autoload.php';

use dam1r89\SimpleBlog\SimpleBlog;
use Symfony\Component\Yaml\Parser;

$cwd = '.';
if (isset($argv[1])){
  $cwd = trim($argv[1], '/');
}


$configPath = getcwd().'/'.$cwd.'/'.CONFIG_FILE;

if (!is_file($configPath)){
  echo "Config '$configPath' file not found.\nShould be named simpleblog.yml\n";
  exit();
}

$sb = new SimpleBlog($configPath, new Parser());
$sb->build();
$log = $sb->getLog();
if (count($log)){
  echo implode("\n", $log)."\n";
}

echo "Successfully build\n";


  