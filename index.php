<?php

define('PAGES_DIR', 'pages');
define('WRAPPER_PAGE', 'public/index.html');

Class SimpleBlog{
  private $pages = array();
  private $handlers;


  public function __construct(){
    $this->handlers = array(
        'list' =>function($t, $matches, &$scope){
          ob_start();
          include 'pieces/list.html';
          return ob_get_clean();

        },
        'content' => function($t, $matches, &$scope){
          // when inserting content put vars from current scope (if available)
          if (isset($scope['content'])){
            return $t->parseContent($scope['content'], $scope)['content'];
          }
        },
        '.*' => function($t, $matches, &$scope){
          if ($matches[2][0]!==''){
            $scope[$matches[1][0]] = $matches[2][0];
            return '';
          }

          if (isset($scope[$matches[1][0]])){
            return $scope[$matches[1][0]];
          }
          return $matches[0][0];
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

      $newScope = array();
      $rawContent = file_get_contents(PAGES_DIR.'/'.$file);
      $scope = $this->parseContent($rawContent, $newScope);
      $this->pages[$scope['route']] = $scope;
    }
  }
  private function parseContent($content, $scope){
      // cita dodele
      $pattern = "/\{\{([^:}]*):?(.*)\}\}/i";
      $offset = 0;
      while(preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE, $offset)){
        $property = $matches[1][0];
        foreach ($this->handlers as $handlerPattern => $handler) {
          $handlerPattern = '/'.$handlerPattern.'/i';
          if (preg_match($handlerPattern, $property, $arguments)){
            $return = $handler($this, $matches, $scope);
            break;
          }
        }
        $offset = $matches[0][1]  + strlen($return);
        $content = preg_replace($pattern, $return, $content, 1);
      };
      $scope['content'] = $content;
      return $scope;
  }

  public function getPage($route){

    $route = $route  === '' ? '.' : $route;
    return isset($this->pages[$route]) ? $this->pages[$route] : null;
  }


  public function renderPage($route){
    $pageScope = $this->getPage($route);
    if ($pageScope===null) $this->send404();
    return $this->parseContent(file_get_contents(WRAPPER_PAGE), $pageScope)['content'];

  }

}

$sb = new SimpleBlog();

$r = isset($_GET['r'])?$_GET['r']:'';

echo $sb->renderPage($r);

?>
