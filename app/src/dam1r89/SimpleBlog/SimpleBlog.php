<?php

namespace dam1r89\SimpleBlog;

Class SimpleBlog {

    private $log;
    private $parser;
    private $conifg;

    function __construct($configPath, $parser) {
        $this->log = array();

        $this->parser = $parser;
        $this->configPath = $configPath;

        $this->readConfig();
        $this->scan();
    }

    private function readConfig() {

        $rawConfig = file_get_contents($this->configPath);

        $parsedConfig = $this->parser->parse($rawConfig);

        $config = $this->normalizeConfig($this->configPath, $parsedConfig);

        $validConfig = $this->checkConfig($config);

        if ($validConfig) {
            $this->config = $config;
        }
    }

    private function scan(){

    	if (!isset($this->config)) {
            return;
        }

        $scanner = new Scanner();

        $engines = array('md', function($input) {

	        return Markdown::defaultTransform($input);
	    });


        $this->blog = $scanner->create($this->config);
        $this->blog->setEngines($engines);  	

        $this->log = array_merge($this->log, $scanner->getLog());

    }

    public function build() {

        $builder = new Builder($this->config);

        $builder->build($this->blog);

    }

	public function getLog(){
		return $this->log;
	}
	
    public function getPage($route){

    	$page = new Page($this->blog, $route);

    	return $page->render();
    }

    private function checkConfig($config) {
        $valid = true;
        $required = array('output', 'pages', 'assets', 'pieces', 'layouts');
        foreach ($required as $value) {

            if (!array_key_exists($value, $config)) {
                $this->log[] = "Missing '$value' in config file.";
                $valid = false;
                continue;
            }
            $path = $config[$value];
            if (!file_exists($path)) {
                $this->log[] = "File/Folder does not exist '$path' in as a '$value' parameter in config file.";
                $valid = false;
            }
        }
        return $valid;
    }

    private function normalizeConfig($configPath, $rawConfig) {

        $base = pathinfo($configPath, PATHINFO_DIRNAME);

        $config = array();
        foreach ($rawConfig as $key => $value) {

            //continue on absolute paths
            if (substr($value, 0, 1) == '/') {
                $config[$key] = $value;
                continue;
            }
            $config[$key] = $base . '/' . $value;

        }
        return $config;
    }

}
