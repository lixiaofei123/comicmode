<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\ComicMode\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'resources' => [
		'record_api' => ['url' => '/api/0.1/records']
	],
    'routes' => [
       ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
       ['name' => 'page#read', 'url' => '/read', 'verb' => 'GET'],
       ['name' => 'comic_api#index', 'url' => '/api/1.0/list.json', 'verb' => 'GET'],
       ['name' => 'comic_api#preflighted_cors', 'url' => '/api/0.1/{path}','verb' => 'OPTIONS', 'requirements' => ['path' => '.+']]
    ]
];
