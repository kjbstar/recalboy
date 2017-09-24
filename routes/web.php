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
    return view('index', ['refresh'=> getenv('REFRESH_AUTO'), 'refresh_delay' => getenv('REFRESH_DELAY'), 'demo_duration'=> getenv('DEMO_DURATION')]);
});

$app->group(['prefix' => 'action'], function($app)
{
    $app->get('{type}','ActionController@action');
});

$app->group(['prefix' => 'game'], function($app)
{
    $app->get('demo/launch','DemoController@launch');
    $app->get('check','GameController@check');
    $app->get('demo/off','DemoController@off');
    $app->get('demo/kill','DemoController@kill');
    $app->get('demo/player','DemoController@checkPlayer');
    $app->get('demo/voldown', function() {
        $config = App\Models\Recalbox\Configuration::setValue('audio.volume', getenv('DEMO_VOLDOWN'));
        return $config;
    });
    $app->get('demo/volup', function() {
        $config = App\Models\Recalbox\Configuration::setValue('audio.volume', getenv('DEMO_VOLUP'));
        return $config;
    });        
    $app->get('backup/sync/{method}/{system}/{game}','BackupController@getGameSave');
});

$app->group(['prefix' => 'config'], function($app)
{
    $app->get('','ConfigController@index');
    $app->get('history','ConfigController@history');
    $app->post('','ConfigController@update');
    $app->get('check/retroarch/networkcommands', ['as' => 'networkcommands', function() {
    	$config = App\Models\Recalbox\Configuration::enableRetroarchNetworkCommands();
    	return $config;
    }]);
    $app->get('cache/clear','ConfigController@cacheClear');
});

$app->group(['prefix' => 'backups'], function($app)
{
    $app->get('','BackupController@backupsManager');
    $app->get('games/saves','BackupController@gamesSavesManager');
    $app->post('games/saves/restore','BackupController@restoreFile');
    $app->get('games/saves/listing','BackupController@listGamesSaves');
});
