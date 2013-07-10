<?php
define('PAGES_DIR', 'pages');
Class SimpleBlog{
  private $routes = array();
  private $handlers;
  public function __construct(){
    $this->handlers = array(
        'route' => function($t, $matches, &$route){

          $newRoute = trim(str_replace(['\'','"'], '', $matches[1]));
          $t->routes[$newRoute] = $t->routes[$route];
          unset($t->routes[$route]);
          $route = $newRoute;
        },
        'title' => function($t, $matches, $route){
          $t->routes[$route]['title'] = $matches[2];
        },


      );
    $this->scanPages();
  }

  public function send404(){
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    die();
  }

  private function  scanPages(){
    $files = scandir(PAGES_DIR);
    foreach ($files as $file) {
      if (substr($file,0,1)==='.') continue;
      $fileContent = file_get_contents(PAGES_DIR.'/'.$file);

      $route = rand();
      $this->routes[$route] = array('content' => $fileContent);

      foreach ($this->handlers as $property => $handler) {

        preg_match("/\{\{{$property}\s?:\s?(.*)\}\}/i", $fileContent, $matches);
        if (isset($matches[1])){
          var_dump($matches);
          $handler($this, $matches, $route);
        }
        $fileContent = preg_replace("/\{\{{$property}\s?:\s?(.*)\}\}/i", '', $fileContent);
      }

      //clan all
      $this->routes[$route]['content'] = preg_replace("/{{(.*)}}/i", '', $this->routes[$route]['content']);

    }
  }

  public function getPage($route){
    return isset($this->routes[$route]) ? $this->routes[$route] : null;
  }

}

$sb = new SimpleBlog();
$r = $_GET['r'];
$page = $sb->getPage($r);
if ($page === null) $sb->send404();

$wrapper = file_get_contents('public/index.html');

while(preg_match("/{{(.*)}}/i", $wrapper, $matches)){
  if ($matches[1]){
    $property = $matches[1];
    if (isset($page[$property])){
      var_dump($page[$property]);
      $wrapper = preg_replace("/\{\{{$property}\}\}/i", $page[$property], $wrapper);
    }
  }
}


echo $wrapper;
?>
