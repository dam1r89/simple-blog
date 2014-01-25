<?php 
namespace dam1r89\SimpleBlog;

Class Page{
  private $page;
  private $engine;
  private $pages;

  public function __construct($config, $page, $pages, $engines){
    $this->config = $config;
    $this->page = $page;
    $this->pages = $pages;
    $this->engines = $engines;
  }

  private function content(){
    foreach ($this->engines as $extension => $engine) {

    }

    $page = $this->page;
    $content = $page['content'];
    foreach (array_reverse($page['extensions']) as $extension) {

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
    echo $content;
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

    include $this->config['pieces'].'/'.$name.'.php';
  } 

  public function render(){

    ob_start();
    include $this->config['layout'];
    return ob_get_clean();    
  }
}

