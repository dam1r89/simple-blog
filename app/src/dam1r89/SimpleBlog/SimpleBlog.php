<?php
namespace dam1r89\SimpleBlog;
use dam1r89\FileSystem;

Class SimpleBlog{
  private $pages = array();
  private $handlers;
  private $engine;
  private $outFolder;
  private $set;


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
    
    $this->handlers = array('content' => function($simpleBlog, $matches, &$scope){
          // when inserting content put vars from current scope (if available)
          if (isset($scope['content'])){
            $newScope = $simpleBlog->compile($scope['content'], $scope);
            return $newScope['content'];
          }
        },
        '.*' => function($simpleBlog, $matches, &$scope){
          if ($matches[2][0]!==''){
            $scope[$matches[1][0]] = $matches[2][0];
            return '';
          }

          if (isset($scope[$matches[1][0]])){
            return $scope[$matches[1][0]];
          }
          return $matches[0][0];
        });
    $this->outFolder = $build ? STATIC_DIR : WORKING_DIR;

  }

  public function getPages(){
    return $this->pages;
  }

  public function getOutputFolder(){
    return $this->outFolder;
  }

  public function addHandler($handler){
    $this->handlers = array_merge($handler, $this->handlers);
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
  /**
   * Skenira sve stranice i kompajlira ih
   */
  public function  scanPages(){

    $files = FileSystem::recursiveScan(PAGES_DIR);
     
    foreach ($files as $file) {


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
       */
      if ($route === null){
        echo "<div style=\"position:fixed;overflow:hidden;top: 0;left: 0;color:white;height:2em;width:100%;background:rgba(200,0,0,0.7)\">Simple Blog: Route is not set in: $file. This file will be omitted.</div>";
        continue;
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
  public function compile($content, $scope){
      /**
       * Cita patterne u obliku {{property:value}} i loopuje kroz sadrzaj
       * i redom primenjuje handler koji matchuje.
       */
      $content = $this->parseHead($content);

      $pattern = "/\{\{([^:}]*):?(.*)\}\}/i";
      $offset = 0;
       
      while(preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE, $offset)){
        $property = $matches[1][0];

        if (!is_array($this->handlers)) throw new Exception("SimpleBlog: Handlers are not set", 1);
        
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
    if ($pageScope===null) return null;
    $pageScope['content'] = $engine($pageScope['content']);
    
    $newScope = $this->compile(file_get_contents(LAYOUT_PAGE), $pageScope);
    return $newScope['content'];

  }
  /**
   * Parsuje heder stranice po uzoru na docpad
   * @param  String $input
   * @return String
   */
  public function parseHead($input){
    $blockPattern = "/(^|\n)---(.*)\n---/s";

    preg_match($blockPattern, $input, $matches);
    
    $output = array();
    
    if (!isset($matches[2])) return $input;

    $block = $matches[0];
    $blockContent = $matches[2];
    
    $lines = explode("\n", $blockContent);
    foreach ($lines as $i => $line) {
      $trimmed = trim($line);
      $keyVal = explode(':', $line);
      if (isset($keyVal[1])){
        $output[] = sprintf('{{%s:%s}}', $keyVal[0], trim($keyVal[1]));
      }
    }

    $output = implode("\n", $output);
    
    return str_replace($block, $output, $input);
  }

  public function setEngine($engine){
    $this->engine = $engine;
  }

}