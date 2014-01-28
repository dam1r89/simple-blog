<?php

date_default_timezone_set('UTC');

define('CONFIG_FILE', '../work/simpleblog.yml');

require 'vendor/autoload.php';

use dam1r89\SimpleBlog\SimpleBlog;
use \Michelf\Markdown;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;


$base = pathinfo(CONFIG_FILE, PATHINFO_DIRNAME);

$yaml = new Parser();
$config = $yaml->parse(file_get_contents(CONFIG_FILE));

foreach ($config as $key => $value) {
  if (substr($value, 0,1) == '/') continue;
  $config[$key] = $base .'/'.$value;
}
// TODO: Check if config is valid
// TODO: Refatctor this

/**
 * Ako sarzi parametar r znaci da treba da odmah kompajlira fajlove
 * i odmah izbaci na out. Ovako se zove iz template foldera, redirektuje
 * requestovanu stranicu na r=<stranica>. Ako nije pozvan sa parametrom
 * onda builduje staticke stranice u public folder.
 */

$build = !isset($_GET['r']);

$r = $build ? '' : $_GET['r'];

$simpleBlog = new SimpleBlog($config);

$simpleBlog->addEngine('md', function($input){
	return Markdown::defaultTransform($input);

});


$log = $simpleBlog->getLog();

if (count($log)){
  echo "<div style=\"position:relative;overflow:hidden;top: 0;left: 0;color:white;width:100%;background:rgba(200,0,0,0.4)\">".implode('<br>', $log)."</div>";
}

if ($build){
  $simpleBlog->build();
  header('Location: ..');
}
else{

  $output = $simpleBlog->renderPage($r);
  if ($output){
  	echo $output;
  }
  else{
    $file = ($config['assets'].'/'.$r); 
    if (is_file($file)){

      $extension = pathinfo($file, PATHINFO_EXTENSION);
      header('Content-Type: '.getMime($extension));
      echo file_get_contents($file);
    }
    else{
      header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
      echo '<div style="font-family: Arial; font-size: 28px; width: 300px; margin: 80px auto; border: 1px solid #ccc; background: #eee; text-align: center; color: #666; border-radius: 4px; box-shadow: 0 0 3px #999; padding: 80px">Page not found :(';
      
    }

  }
}

function getMime($extension){

  switch ($extension){
    case 'js':
      return 'application/javascript';
    case 'css':
      return 'text/css';
    default:
      return 'text/plane';

  }

}
