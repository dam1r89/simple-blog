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
		$configPath = rtrim($argFolder, '/');
	}
	else{
		// relative path
		$configPath = getcwd().'/'.trim($argFolder,'/');
	}
}
else{
	// no path specified... get current
	$configPath = getcwd();
}

$config = array(
	'output' 	=> './static',
	'pages'		=> './pages',
	'assets'	=> './app',
	'pieces'	=> './pieces',
	'layouts'	=> './app'
);

$configContent = '';
foreach ($config as $key => $value) {
	$folder = $configPath.'/'.$value;
	if (!file_exists($folder)){
		mkdir($folder);
	}
	$configContent .= "$key\t: $value\n";
}
file_put_contents($configPath.'/simpleblog.yml', $configContent);

copy(__DIR__.'/templates/.htaccess', $configPath.'/.htaccess');
copy(__DIR__.'/templates/index.php', $configPath.'/index.php');

echo "Success!\n";
