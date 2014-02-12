<?php

namespace dam1r89\SimpleBlog;

Class Page {

    private $page;
    private $pages;
    private $layout;
    private $blog;

    public function __construct(Blog $blog, $route) {

        $this->blog = $blog;
        $this->pages = $blog->getPages();
        $this->page = $this->getPage($route);
        

        $layouts = $blog->getLayouts();

        $layoutName = isset($this->page['layout']) ? $this->page['layout'] : 'index';
        $this->layout = $layouts[$layoutName];

    }

    private function content() {

        $page = $this->page;
        echo $this->process($page);
    }

    private function process($page) {
        
        $content = $page['content'];
        $reversed = array_reverse($page['extensions']);
        $engines = $this->blog->getEngines();
        foreach ($reversed as $extension) {

            if ($extension === 'php') {
                $content = $this->phpEngine($content);
                continue;
            }

            if (!isset($engines[$extension])) {
                continue;
            }

            $engine = $engines[$extension];
            $content = $engine($content);
        }
        return $content;

    }

    private function prop($key) {

        echo isset($this->page[$key]) ? $this->page[$key] : '';
    }

    private function phpEngine($input) {

        ob_start();
        eval('?>' . $input);
        return ob_get_clean();
    }

    private function piece($name) {
        $pieces = $this->blog->getPieces();
        include $pieces[$name]['path'];
    }

    /**
     * Returns appropriate page defined with required route.
     * @param  String $route Route that is defined in page yaml block
     * @return Array         Page with parameters
     */
    public function getPage($route) {

        $route = $route === '' ? '.' : $route;
        return isset($this->pages[$route]) ? $this->pages[$route] : null;
    }

    public function render() {

        ob_start();
        include $this->layout['path'];
        return ob_get_clean();
    }

}
