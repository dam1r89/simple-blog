#!/usr/bin/php -q
<?php

define('CONFIG_FILE', 'simpleblog.yml');

date_default_timezone_set('UTC');

require 'vendor/autoload.php';

use dam1r89\SimpleBlog\SimpleBlog;
use Symfony\Component\Yaml\Parser;

if (isset($argv[1])){

	$argFolder =$argv[1];
	// absolute path
	if (substr($argFolder, 0,1) == '/'){
		$configPath = rtrim($argFolder, '/').'/'.CONFIG_FILE;
	}
	else{
		// relative path
		$configPath = getcwd().'/'.trim($argFolder,'/').'/'.CONFIG_FILE;
	}
}
else{
	// no path specified... get current
	$configPath = getcwd().'/'.CONFIG_FILE;
}



if (!is_file($configPath)){
  echo "Config '$configPath' file not found.\nShould be named simpleblog.yml\n";
  exit();
}

$sb = new SimpleBlog($configPath, new Parser());
$sb->build();

if (count($sb->getLog())){
  echo implode("\n", $sb->getLog())."\n";
}

echo "Successfully build\n";


