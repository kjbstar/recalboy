<?php

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

$app->get('/', function() {
	// TODO : faire un système de templates pour ne pas être restreint de la seule grille
    return view(getenv('THEME'), ['refresh'=> getenv('REFRESH_AUTO'), 'refresh_delay' => getenv('REFRESH_DELAY')]);
});

$app->group(['prefix' => 'action'], function($app)
{
    $app->get('{type}','ActionController@action');
});

$app->group(['prefix' => 'game'], function($app)
{
    $app->get('check','GameController@check');
});