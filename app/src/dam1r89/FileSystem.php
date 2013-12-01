<?php

namespace dam1r89;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \FilesystemIterator;

Class FileSystem{
  public static function recursiveDelete($dirPath)
  {
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
    $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
}
rmdir($dirPath);

  }
  
    public static function recursiveCopy($src,$dst) { 
      $dir = opendir($src); 
      @mkdir($dst); 
      while(false !== ( $file = readdir($dir)) ) { 
          if (( $file != '.' ) && ( $file != '..' )) { 
              if ( is_dir($src . '/' . $file) ) { 
                  self::recursiveCopy($src . '/' . $file,$dst . '/' . $file); 
              } 
              else { 
                  copy($src . '/' . $file,$dst . '/' . $file); 
              } 
          } 
      } 
      closedir($dir); 
  } 

  public static function recursiveScan($path){
    $allFiles = array();
    foreach (scandir($path) as $fileName) {
      if (substr($fileName,0,1)==='.') continue;
      $file = $path.'/'.$fileName;
      if (is_dir($file)){
        $allFiles = array_merge($allFiles, self::recursiveScan($file));
        continue;
      }
      $allFiles[] = $file;
    }
    return $allFiles;
  }

}