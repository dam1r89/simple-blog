<?php

define('PAGES_DIR', 'template/pages');
define('STATIC_DIR', 'public');
define('ASSETS_DIR', 'template/assets');
define('WRAPPER_PAGE', 'template/index.html');
define('PIECES_DIR', 'template/pieces');

include 'Markdown.php';
use \Michelf\Markdown;

Class FileSystem{
  public static function recursiveDelete($path)
  {
    return is_file($path)?
      @unlink($path):
      array_map('FileSystem::recursiveDelete',glob($path.'/*'))==@rmdir($path)
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
  public static function recursiveScan($path){
    $allFiles = array();
    foreach (scandir($path) as $fileName) {
      if (substr($fileName,0,1)==='.') continue;
      $file = $path.'/'.$fileName;
      if (is_dir($file)){
        $allFiles = array_merge($allFiles, self::recursiveScan($file));
        continue;
      }
      $allFiles[] = $file;
    }
    return $allFiles;
  }

}


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

          foreach ($t->pages as $route => $page) {
            if ($page['title'] === $matches[2][0]){
              return $page['route'];
            }
          }
          return $matches[0][0];
        },
        'content' => function($t, $matches, &$scope){
          // when inserting content put vars from current scope (if available)
          if (isset($scope['content'])){
            return $t->compile($scope['content'], $scope)['content'];
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

  public function build(){
    $cachedPages = array();
    FileSystem::recursiveDelete(STATIC_DIR);
    FileSystem::recursiveCopy(ASSETS_DIR, STATIC_DIR, '.');
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
    $files = FileSystem::recursiveScan(PAGES_DIR);
    foreach ($files as $file) {
      if (substr($file,0,1)==='.') continue;

      $newScope = array();
      $rawContent = file_get_contents($file);
      $scope = $this->compile($rawContent, $newScope);
      $route = isset($scope['route']) ? $scope['route'] : null;
      if ($route === null){
        $route = '_';
        echo "<div style=\"position:fixed;overflow:hidden;top: 0;left: 0;color:white;height:2em;width:100%;background:rgba(200,0,0,0.7)\">Route in file: $file is not defined</div>";
      }
      $this->pages[$route] = $scope;
    }
  }
  private function compile($content, $scope){
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
    $pageScope['content'] = Markdown::defaultTransform($pageScope['content']);
    if ($pageScope===null) $this->send404();
    return $this->compile(file_get_contents(WRAPPER_PAGE), $pageScope)['content'];

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
