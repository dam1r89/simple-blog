<?php
define('PAGES_DIR', 'pages');
Class SimpleBlog{
  private $routes = array();
  private $handlers;
  public function __construct(){
    $this->handlers = array(
        'list' =>function($t, $matches, &$route){
          return '<ul> <li>Item</li> </ul>';
        },
        '.*' => function($t, $matches, &$page){
          $page[$matches[1]] = $matches[2];
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

      // cita dodele
      $page = array();
      $pattern = "/\{\{(.*)\s?:\s?(.*)\}\}/i";

      while(preg_match($pattern, $fileContent, $matches)){
        $property = $matches[1];
        foreach ($this->handlers as $handlerPattern => $handler) {
          $handlerPattern = '/'.$handlerPattern.'/i';
          if (preg_match($handlerPattern, $property, $arguments)){
            $return = $handler($this, $matches, $page);
            break;
          }
        }

        $fileContent = preg_replace($pattern, $return, $fileContent, 1);
      };
      $page['content'] = $fileContent;
      $this->routes[$page['route']] = $page;


    }
  }

  public function getPage($route){
    return isset($this->routes[$route]) ? $this->routes[$route] : null;
  }

}

$sb = new SimpleBlog();
$r = isset($_GET['r'])?$_GET['r']:'';
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
