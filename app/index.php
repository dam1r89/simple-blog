<?php

date_default_timezone_set('UTC');

define('STATIC_DIR', '../static');
define('WORKING_DIR', '../work-tmpl');
define('PAGES_DIR', WORKING_DIR.'/pages');
define('ASSETS_DIR', WORKING_DIR.'/assets');
define('PIECES_DIR', WORKING_DIR.'/pieces');
define('LAYOUT_PAGE', WORKING_DIR.'/layouts/default.php');

require 'vendor/autoload.php';

use dam1r89\SimpleBlog\SimpleBlog;
use dam1r89\SimpleBlog\Handlers;
use dflydev\markdown\MarkdownParser;



/**
 * Ako sarzi parametar r znaci da treba da odmah kompajlira fajlove
 * i odmah izbaci na out. Ovako se zove iz template foldera, redirektuje
 * requestovanu stranicu na r=<stranica>. Ako nije pozvan sa parametrom
 * onda builduje staticke stranice u public folder.
 */

$build = !isset($_GET['r']);

$r = $build ? '' : $_GET['r'];

$simpleBlog = new SimpleBlog();

$simpleBlog->addEngine('md', function($input){
	
	$markdownParser = new MarkdownParser();
	return $markdownParser->transformMarkdown($input);

});


$simpleBlog->scanPages();
$logs = $simpleBlog->getLog();
if (count($logs)){
  echo "<div style=\"position:relative;overflow:hidden;top: 0;left: 0;color:white;width:100%;background:rgba(200,0,0,0.4)\">".implode('<br>', $logs)."</div>";
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
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    echo '<div style="font-family: Arial; font-size: 28px; width: 300px; margin: 80px auto; border: 1px solid #ccc; background: #eee; text-align: center; color: #666; border-radius: 4px; box-shadow: 0 0 3px #999; padding: 80px">Page not found :(';
  }
}

