<?php
namespace dam1r89\SimpleBlog;
use dam1r89\FileSystem;

class Builder {

    private $config;

    public function __construct($config) {
        $this->config = $config;
    }
    
    public function build(Blog $blog) {
        FileSystem::recursiveDelete($this->config['output']);
        FileSystem::recursiveCopy($this->config['assets'], $this->config['output'], '.');

        foreach ($blog->getPages() as $route => $page) {

            $parts = pathinfo($route);

            // Kreira folder u obliku rute
            $path = $this->config['output'] . '/' . $parts['dirname'];

            //I ime fajla
            $out = $path . '/' . ($parts['basename'] == '.' ? 'index.html' : $parts['basename']);

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $page = new Page($blog, $route);

            $content = $page->render();

            file_put_contents($out, $content);
        }
    }

}
