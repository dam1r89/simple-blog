<?php

namespace dam1r89\SimpleBlog;

use dam1r89\FileSystem;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 *
 * @property array $log
 * @property array $config
 * @property Blog $blog
 */
Class Scanner {

    private $log = array();
    private $config;

    public function create($config) {
        $this->config = $config;

        $blog = new Blog();

        $blog->setPages($this->scanPages());
        $blog->setLayouts($this->scanLayouts());
        $blog->setPieces($this->scanPieces());
        return $blog;
    }

    private function scanPieces() {

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

    private function scanLayouts() {

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
    private function scanPages() {

        $pages = array();

        $files = FileSystem::recursiveScan($this->config['pages']);

        foreach ($files as $file) {

            $rawContent = file_get_contents($file);

            try {

                $page = $this->parse($rawContent);
            } catch (ParseException $e) {

                $this->log[] = sprintf("YAML block parsing failed: %s", $e->getMessage());
            } catch (Exception $e) {

                $this->log[] = springf("Error reading %s. This file will be omitted. %s", $file, $e->getMessage());
            }

            $route = isset($page['route']) ? $page['route'] : null;

            $page['extensions'] = $this->getExtensions($file);

            /**
             * Ako ruta nije definisana pusti upozorenje
             */
            if ($route === null) {
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

    public function getLog() {
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
    private function parse($input) {


        $blockPattern = "/(^|\n)---(.*)\n---/s";

        preg_match($blockPattern, $input, $matches);

        if (!isset($matches[2])) {
            return array();
        }

        $block = $matches[0];
        $blockContent = $matches[2];

        $yaml = new Parser();
        $parameters = $yaml->parse($blockContent);

        $parameters['content'] = str_replace($block, '', $input);

        return $parameters;
    }

}
