<?php 
namespace dam1r89\SimpleBlog;

Class Page{
  private $page;
  private $pages;
  private $pieces;
  private $engines;
  private $layout;

  public function __construct($pages, $page, $layouts, $pieces, $engines){

    $layoutName = isset($page['layout']) ? $page['layout'] : 'index';

    $this->layout = $layouts[$layoutName];
    $this->page = $page;
    $this->pages = $pages;
    $this->pieces = $pieces;
    $this->engines = $engines;


  }

  private function content(){

    $page = $this->page;
    echo $this->process($page);
  }

  private function process($page){
    $content = $page['content'];
    $reversed = array_reverse($page['extensions']);
    foreach ($reversed as $extension) {

      if ($extension === 'php'){
        $content = $this->phpEngine($content);
        continue;
      }

      if (!isset($this->engines[$extension]))
      {
        continue; 
      } 

      $engine =  $this->engines[$extension];
      $content = $engine($content);

    }
    return $content;

  }

  private function prop($key){

    echo isset($this->page[$key]) ? $this->page[$key] : '';

  }

  private function phpEngine($input){

    ob_start();
    eval('?>'.$input);
    return ob_get_clean();

  }

  private function piece($name){

    include $this->pieces[$name]['path'];

  } 

  public function render(){

    ob_start();
    include $this->layout['path'];
    return ob_get_clean();    

  }
}

