<?php
define('PAGES_DIR', 'pages');
Class SimpleBlog{
  private $routes = array();
  private $handlers;
  public function __construct(){
    $this->handlers = array(
        'route' => function($t, $matches, &$route){

          $newRoute = trim(str_replace(['\'','"'], '', $matches[2]));
          $t->routes[$newRoute] = $t->routes[$route];
          unset($t->routes[$route]);
          $route = $newRoute;

        },
        '.*' => function($t, $matches, $route){
          $t->routes[$route][$matches[2]] = $matches[2];
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
      // cita dodele

      $pattern = "/\{\{(.*)\s?:\s?(.*)\}\}/i";

      while(preg_match($pattern, $fileContent, $matches)){
        $property = $matches[1];
        foreach ($this->handlers as $handlerPattern => $handler) {
          $handlerPattern = '/'.$handlerPattern.'/i';
          if (preg_match($handlerPattern, $property, $arguments)){
            $handler($this, $matches, $route);
            break;

          }
        }
        $fileContent = preg_replace($pattern, '', $fileContent, 1);
      };

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
    $wrapper = preg_replace("/\{\{{$property}\}\}/i", isset($page[$property]) ? $page[$property] : '', $wrapper);
  }
}


echo $wrapper;
?>
