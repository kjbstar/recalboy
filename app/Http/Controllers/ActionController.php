<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class ActionController extends BaseController
{

	private $output_screenshot_name;

    public function action($message) {
		
		if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
		    socket_sendto($socket, $message, strlen($message), 0, getenv('RECALBOX_IP'), 55355);
		    $res = $message.' envoyé !';
		    if ($message === 'SCREENSHOT' && getenv('UPLOAD_SCREENSHOTS') == 1) {
		    	self::uploadScreenshot();
		    }
		} else {
		  $res = 'connection à recalbox impossible';
		}
    	return response()->json($res);
    	
    }

    public function uploadScreenshot() {

    	// On laisse le temps au Rpi de faire le screenshot
    	sleep(3);

    	// On recupère le dernier fichier enregistré
    	$commande = 'cd '.getenv('RECALBOX_SCREENSHOTS_PATH').' && ls -t | head -n1';

		\SSH::run($commande, function($output_screenshot_name){
			$this->output_screenshot_name = $output_screenshot_name;
		});    	
		$filename = $this->output_screenshot_name;
		// Espace en fin de string récupéré...
		$filename = rtrim($filename);

		// Maintenant on le récupère pour pouvoir l'uploader
    	$remote = getenv('RECALBOX_SCREENSHOTS_PATH').'/'.$filename;
    	$fichier = \Storage::put('screenshots/'.$filename, 1);
    	$local = storage_path('app/public/screenshots/'.$filename);
    	\SSH::into('recalbox')->get($remote, $local);	

    	$screen = \Storage::get('screenshots/'.$filename);
    	\Storage::disk(getenv('UPLOAD_METHOD'))->put(getenv('UPLOAD_REMOTE_PATH').'/'.$filename, $screen, 'public');

    }

}
