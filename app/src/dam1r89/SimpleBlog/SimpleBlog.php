<?php
namespace dam1r89\SimpleBlog;
use dam1r89\FileSystem;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

Class SimpleBlog{

  private $log = array();
  public $config;

  public function __construct($config){
    $this->config = $config;
    $this->pages = $this->scanPages();
    $this->layouts = $this->scanLayouts();
    $this->pieces = $this->scanPieces(); 

  }

  public function build(){

    FileSystem::recursiveDelete($this->config['output']);
    FileSystem::recursiveCopy($this->config['assets'], $this->config['output'], '.');
    
    foreach ($this->pages as $route => $page) {
      
      $parts = pathinfo($route);

      // Kreira folder u obliku rute
      $path = $this->config['output'] . '/' . $parts['dirname'];

      
      //I ime fajla
      $out = $path . '/' . ($parts['basename'] == '.' ? 'index.html' : $parts['basename']);

      if (!file_exists($path)){
        mkdir($path, 0777, true);
      }

      file_put_contents($out, $this->renderPage($route));

    }
  }

  public function scanPieces(){

    $pieces = array();

    $files = FileSystem::recursiveScan($this->config['pieces']);
     
    foreach ($files as $file) {
      
      $name = pathinfo($file, PATHINFO_FILENAME);

      $pieces[$name] = array(
        'path' => $file,
        'name' => $name
      );

    }
    return $pieces;
   
  } 

  public function scanLayouts(){

    $layouts = array();

    $files = FileSystem::recursiveScan($this->config['layouts']);
     
    foreach ($files as $file) {
      
      $name = pathinfo($file, PATHINFO_FILENAME);

      $layouts[$name] = array(
        'path' => $file,
        'name' => $name
      );

    }
    return $layouts; 
  } 

  /**
   * Scan all pages from defined directory and parse them
   * @return [type] [description]
   */
  public function scanPages(){

    $pages = array();

    $files = FileSystem::recursiveScan($this->config['pages']);
     
    foreach ($files as $file) {
      
      $rawContent = file_get_contents($file);
      
      try {

        $page = $this->parse($rawContent);

      } catch (ParseException $e) {

        $this->log[] = sprintf("YAML block parsing failed: %s",$e->getMessage());

      } catch (Exception $e) {

        $this->log[] = springf("Error reading %s. This file will be omitted. %s", $file, $e->getMessage());

      }

      $route = isset($page['route']) ? $page['route'] : null;

      $page['extensions'] = $this->getExtensions($file);
        
      /**
       * Ako ruta nije definisana pusti upozorenje
       */
      if ($route === null){
        $this->log[] = "Route is not set in: '$file'. This file will be omitted.";
        continue;
      }


      // Dodaje sve u array svih stranica
      $pages[$route] = $page;

    }
    return $pages;
  }

  private function getExtensions($file) { 
      $extensions = explode('.', pathinfo($file, PATHINFO_BASENAME));
      array_shift($extensions);
      return $extensions;

  }

  public function getLog(){
    return $this->log;
  }
  /**
   * Parse yaml block of the file and returns array with parameters that are specified
   * in the block. Additional property is content which has a content with removed
   * yaml block
   * 
   * @param  String $input  Input, content of a file 
   * @return Array          Parsed parameters 
   */
  public function parse($input){

    $parameters = array();
    $blockPattern = "/(^|\n)---(.*)\n---/s";

    preg_match($blockPattern, $input, $matches);
    
    if (!isset($matches[2])) return $parameters;

    $block = $matches[0];
    $blockContent = $matches[2];
    
    $yaml = new Parser();
    $parameters = $yaml->parse($blockContent);

    $parameters['content'] = str_replace($block, '', $input);

    return $parameters;
  }

  /**
   * Returns appropriate page defined with required route.
   * @param  String $route Route that is defined in page yaml block
   * @return Array         Page with parameters
   */
  public function getPage($pages, $route){

    $route = $route  === '' ? '.' : $route;
    return isset($pages[$route]) ? $pages[$route] : null;
  }

  /**
   * Renders a page by processing it trough all defined engines
   * @param  String $route Route of a page that needs to be rendered
   * @return String        Rendered page with layout
   */
  public function renderPage($route){

    $page = $this->getPage($this->pages, $route);

    if ($page===null)
      return null;


    $p = new Page($this->pages, $page, $this->layouts, $this->pieces, $this->engines);

    return $p->render();

  }

  public function addEngine($extension, $engine){
    $this->engines[$extension] = $engine;
  }

}
