#!/usr/bin/php -q
<?php

define('CONFIG_FILE', 'simpleblog.yml');

date_default_timezone_set('UTC');

require 'vendor/autoload.php';

use dam1r89\SimpleBlog\SimpleBlog;
use \Michelf\Markdown;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

$cwd = '.';
if (isset($argv[1])){
  $cwd = trim($argv[1], '/');
}


$configPath = getcwd().'/'.$cwd.'/'.CONFIG_FILE;

if (!is_file($configPath)){
  echo "Config '$configPath' file not found.\nShould be named simpleblog.yml\n";
  exit();
}

$base = pathinfo($configPath, PATHINFO_DIRNAME);

$yaml = new Parser();
$rawConfig = $yaml->parse(file_get_contents($configPath));

$config = normalizeConfig($base, $rawConfig);

$simpleBlog = new SimpleBlog($config);

$simpleBlog->addEngine('md', function($input){
  return Markdown::defaultTransform($input);
});

$log = $simpleBlog->getLog();

if (count($log)){
  echo implode("\n", $log)."\n";
}

$simpleBlog->build();

echo "Successfully build to {$config['output']} folder\n";


function normalizeConfig($base, $rawConfig){
  $config = array();
  foreach ($rawConfig as $key => $value) {

    if (substr($value, 0,1) == '/') continue;
    $config[$key] = $base .'/'.$value;

  }
  return $config;
}