<?php

define('PAGES_DIR', 'template/pages');
define('STATIC_DIR', 'public');
define('ASSETS_DIR', 'template/assets');
define('WRAPPER_PAGE', 'template/index.html');
define('PIECES_DIR', 'template/pieces');
Class SimpleBlog{
  private $pages = array();
  private $handlers;


  public function __construct(){
    $this->handlers = array(
        'piece' =>function($t, $matches, &$scope){
          ob_start();
          include PIECES_DIR.'/'.$matches[2][0];
          return ob_get_clean();

        },
        'link' => function($t, $matches, &$scope){
          return 'http://www.google.com';
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
  public static function recursiveDelete($path)
  {
    return is_file($path)?
      @unlink($path):
      array_map('SimpleBlog::recursiveDelete',glob($path.'/*'))==@rmdir($path)
    ;
  }
  public static function recursiveCopy($source, $dest, $diffDir = ''){
      $sourceHandle = opendir($source);
      if(!$diffDir)
              $diffDir = $source;

      mkdir($dest . '/' . $diffDir);

      while($res = readdir($sourceHandle)){
          if($res == '.' || $res == '..')
              continue;

          if(is_dir($source . '/' . $res)){
              self::recursiveCopy($source . '/' . $res, $dest, $diffDir . '/' . $res);
          } else {
              copy($source . '/' . $res, $dest . '/' . $diffDir . '/' . $res);

          }
      }
  }

  public function build(){
    $cachedPages = array();
    SimpleBlog::recursiveDelete(STATIC_DIR);
    SimpleBlog::recursiveCopy(ASSETS_DIR, STATIC_DIR, '.');
    foreach ($this->pages as $route => $page) {
      $parts = pathinfo($route);

      $path = STATIC_DIR . '/' . $parts['dirname'];
      $basename = $path . '/' . ($parts['basename'] === '.' ? 'index.html' : $parts['basename']);

      mkdir($path, 0, true);
      file_put_contents($basename, $this->renderPage($route));
    }
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
$build = !isset($_GET['r']);
$r = $build ? '' : $_GET['r'];

$sb = new SimpleBlog();
if ($build){
  $sb->build();
}
else{
  echo $sb->renderPage($r);
}
?>
