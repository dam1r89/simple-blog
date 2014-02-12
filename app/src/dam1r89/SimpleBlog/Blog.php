<?php
namespace dam1r89\SimpleBlog;

class Blog {
    
    	protected $pages;
    	protected $layouts;
    	protected $pieces;
    	protected $engines;
        
        public function getPages() {
            return $this->pages;
        }

        public function getLayouts() {
            return $this->layouts;
        }

        public function getPieces() {
            return $this->pieces;
        }

        public function getEngines() {
            return $this->engines;
        }

        public function setPages($pages) {
            $this->pages = $pages;
        }

        public function setLayouts($layouts) {
            $this->layouts = $layouts;
        }

        public function setPieces($pieces) {
            $this->pieces = $pieces;
        }

        public function setEngines($engines) {
            $this->engines = $engines;
        }



}