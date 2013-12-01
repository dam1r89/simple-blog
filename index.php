<?php

define('PAGES_DIR', 'template/pages');
define('STATIC_DIR', 'public');
define('DEVELOP_DIR', 'template');
define('ASSETS_DIR', 'template/assets');
define('LAYOUT_PAGE', 'template/index.html');
define('PIECES_DIR', 'template/pieces');

include 'Markdown.php';
include 'FileSystem.php';

use \Michelf\Markdown;


Class SimpleBlog{
  private $pages = array();
  private $handlers;
  private $engine;
  private $outFolder;


  public function __construct($build){
    /**
     * Hendleri kojima je prosledjen sadrzaj podatka koji je nadjen u
     * template-u u obliku {{property:value}}. Selector handlera sluzi
     * da se proveri da li property matchuje sa selectorom. Handler nakon obrade
     * treba da vrati sadrzaj koji ce doci na to mesto. Svakom handleru
     * je prosledjen $this objekat, $matches - delovi parsovanog stringa
     * (string u zagradicama) i referenca na scope.
     * Handleri probadaju od vrha na dole.
     * @var array
     */
    // TODO: izbaciti handlere iz objekta
    $this->handlers = array(
        'piece' =>function($t, $matches, &$scope){
          ob_start();
          include PIECES_DIR.'/'.$matches[2][0].'.php';
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
        'base' => function($t){

          $basePath = $_SERVER['PHP_SELF'];
          $pos = strrpos($basePath, '/');
          $base = substr($basePath, 0, $pos+1).$t->outFolder.'/'; 
          return sprintf(
            "%s://%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['HTTP_HOST'],
            $base
          );

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

    $this->outFolder = $build ? STATIC_DIR : DEVELOP_DIR;

    /**
     * Inicijalno skenira sve stranice.
     */
    $this->scanPages();

  }

  public function build(){

    $cachedPages = array();
    
    // kopira asete u public direktorijum
    FileSystem::recursiveDelete(STATIC_DIR);
    FileSystem::recursiveCopy(ASSETS_DIR, STATIC_DIR, '.');
    
    foreach ($this->pages as $route => $page) {
      
      $parts = pathinfo($route);

      // Kreira folder u obliku rute
      $path = STATIC_DIR . '/' . $parts['dirname'];

      
      //I ime fajla
      $out = $path . '/' . ($parts['basename'] == '.' ? 'index.html' : $parts['basename']);

      if (!file_exists($path)){
        mkdir($path, 0777, true);
      }

      file_put_contents($out, $this->renderPage($route));

    }
  }

  public function send404(){
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    die();
  }

  private function  scanPages(){

    $files = FileSystem::recursiveScan(PAGES_DIR);
    
    foreach ($files as $file) {
      /**
       * Preskace fajlove koji pocinju sa tackom.
       */
      if (substr($file,0,1)==='.') continue;

      $newScope = array();
      $rawContent = file_get_contents($file);

      /**
       * Kompajlira sadrzaj templatea uz pomoc $scope-a i vraca novi $scope
       * koji mora da ima 'route' da bi bio vidljiv. $scope sluzi za prosledjivanje
       * podataka izmedju layouta i dokumenata.
       */
      
      $scope = $this->compile($rawContent, $newScope);
      $route = isset($scope['route']) ? $scope['route'] : null;

      /**
       * Ako ruta nije definisana pusti upozorenje
       * 
       */
      if ($route === null){
        // TODO: zasto stavljam underscore?
        $route = '_';
        echo "<div style=\"position:fixed;overflow:hidden;top: 0;left: 0;color:white;height:2em;width:100%;background:rgba(200,0,0,0.7)\">Route in file: $file is not defined</div>";
      }
      // Dodaje sve u array svih stranica
      $this->pages[$route] = $scope;
    }
  }

  /**
   * Kompajlira - primenjuje odgovarajuce handlere za svaki pronadjen pattern
   * @param  String $content Template stranica
   * @param  Array $scope   Sadrzi promenjive koje se prenose izmedju pages i wrapper-a
   * @return Array          Vraca (moguce izmenjeni) scope
   */
  private function compile($content, $scope){
      /**
       * Cita patterne u obliku {{property:value}} i loopuje kroz sadrzaj
       * i redom primenjuje handler koji matchuje.
       */
      $pattern = "/\{\{([^:}]*):?(.*)\}\}/i";
      $offset = 0;

       
      while(preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE, $offset)){
        $property = $matches[1][0];
        foreach ($this->handlers as $handlerPattern => $handler) {
          $handlerPatternRegEx = '/'.$handlerPattern.'/i';

          // Ako key handlera (property) matchuju primeni handler
          if (preg_match($handlerPatternRegEx, $property)){
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
  /**
   * Public i interna metoda koja vraca rendovanu stranicu sa wrapperom.
   * @param  String $route Ruta fajla koji treba da se izrenda
   * @return String        Izrendani html
   */
  public function renderPage($route){
    
    $engine = $this->engine;

    $pageScope = $this->getPage($route);

    $pageScope['content'] = $engine($pageScope['content']);
    
    if ($pageScope===null) $this->send404();
    return $this->compile(file_get_contents(LAYOUT_PAGE), $pageScope)['content'];

  }

  public function setEngine($engine){
    $this->engine = $engine;
  }

}

/**
 * Ako sarzi parametar r znaci da treba da odmah kompajlira fajlove
 * i odmah izbaci na out. Ovako se zove iz template foldera, redirektuje
 * requestovanu stranicu na r=<stranica>. Ako nije pozvan sa parametrom
 * onda builduje staticke stranice u public folder.
 */

$build = !isset($_GET['r']);

$r = $build ? '' : $_GET['r'];

$simpleBlog = new SimpleBlog($build);

$simpleBlog->setEngine(function($input){
  return Markdown::defaultTransform($input);
});

if ($build){
  $simpleBlog->build();
}
else{
  echo $simpleBlog->renderPage($r);
}
?>
