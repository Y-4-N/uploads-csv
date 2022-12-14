<?php
/** @var \Laravel\Lumen\Routing\Router $app */
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->get('/hello', function () use ($app) {
    return '<h1>Hello world!</h1>';
});

$app->group(['prefix' => '/'], function() use ($app){
    $app->get('create', 'FileEntriesController@create');
    $app->post('upload-file', 'FileEntriesController@uploadFile');
    $app->delete('/{id}', 'FilesController@delete');
    $app->get('{id}', 'FilesController@show');
});
