<?php
namespace dam1r89\SimpleBlog;

Class Handlers {
	public static function getAll(){

		return array(
		        'piece' =>function($simpleBlog, $matches, &$scope){
		          ob_start();
		          include __DIR__.'/pieces/'.$matches[2][0].'.php';
		          return ob_get_clean();

		        },
		        'link' => function($simpleBlog, $matches, &$scope){

		          foreach ($simpleBlog->getPages() as $route => $page) {
		            if ($page['title'] === $matches[2][0]){
		              return $page['route'];
		            }
		          }
		          return $matches[0][0];
		        },
		        'base' => function($simpleBlog){

		          $basePath = $_SERVER['PHP_SELF'];
		          $pos = strrpos($basePath, '/');
		          $base = substr($basePath, 0, $pos+1).$simpleBlog->getOutputFolder().'/'; 
		          return sprintf(
		            "%s://%s%s",
		            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
		            $_SERVER['HTTP_HOST'],
		            $base
		          );

		        },
		        'shareThis' => function($simpleBlog, $matches, &$scope){
		        	return $scope['route'];
		        },
		      );	
		
	}
}
