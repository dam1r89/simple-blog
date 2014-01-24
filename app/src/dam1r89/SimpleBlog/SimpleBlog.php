<?php
namespace dam1r89\SimpleBlog;
use dam1r89\FileSystem;

Class SimpleBlog{
  private $pages = array();
  private $engines = array();
  private $log = array();

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
  public function scanPages(){


    $files = FileSystem::recursiveScan(PAGES_DIR);
     
    foreach ($files as $file) {


      $newScope = array();
      
      $rawContent = file_get_contents($file);
      
      /**
       * Kompajlira sadrzaj templatea uz pomoc $scope-a i vraca novi $scope
       * koji mora da ima 'route' da bi bio vidljiv. $scope sluzi za prosledjivanje
       * podataka izmedju layouta i dokumenata.
       */


      $page = $this->parse($rawContent);
      $route = isset($page['route']) ? $page['route'] : null;

      /**
       * Ako ruta nije definisana pusti upozorenje
       */
      if ($route === null){
        $this->log[] = "Route is not set in: <em>$file</em>. This file will be omitted.";
        continue;
      }

      $extensions = explode('.', pathinfo($file, PATHINFO_BASENAME));
      array_shift($extensions);

      $page['extensions'] = $extensions;

      // Dodaje sve u array svih stranica
      $this->pages[$route] = $page;

    }
    return $this;
  }

  public function getLog(){
    return $this->log;
  }

  /**
   * Kompajlira - primenjuje odgovarajuce handlere za svaki pronadjen pattern
   * @param  String $content Template stranica
   * @param  Array $scope   Sadrzi promenjive koje se prenose izmedju pages i wrapper-a
   * @return Array          Vraca (moguce izmenjeni) scope
   */
  public function parse($input){

    $parameters = array();
    $blockPattern = "/(^|\n)---(.*)\n---/s";

    preg_match($blockPattern, $input, $matches);
    
    
    if (!isset($matches[2])) return $parameters;

    $block = $matches[0];
    $blockContent = $matches[2];
    
    $lines = explode("\n", $blockContent);
    foreach ($lines as $i => $line) {
      $trimmed = trim($line);
      $keyVal = explode(':', $line);
      if (isset($keyVal[1])){
        $parameters[$keyVal[0]] = trim($keyVal[1]);
      }
    }
    $parameters['content'] = str_replace($block, '', $input);

    return $parameters;
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
    

    $page = $this->getPage($route);

    if ($page===null)
      return null;
    // Mozda samo da prosledi rutu, a page da gleda dal postoji

    $p = new Page($page, $this->pages, $this->engines);
    return $p->render();

  }

  public function addEngine($extension, $engine){
    $this->engines[$extension] = $engine;
  }

}
