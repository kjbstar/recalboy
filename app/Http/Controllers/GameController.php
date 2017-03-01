<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class GameController extends BaseController
{

	private $output;

    public function check() {
		
    	$commande = "ps aux | grep 'retroarch -L' | egrep -o ".getenv('RECALBOX_ROMS_PATH')."'/.*'";

		$game = \SSH::run($commande, function($output)
		{	
			$game = self::getGame($output);
			$this->output = self::getInfos($game);
		});
		
		if (is_null($this->output)) {
			$this->output = array("status" => "off");
		}

    	return response()->json($this->output);
    	
    }

    public function getGame($path) {
		
    	$path = explode(getenv('RECALBOX_ROMS_PATH'), $path);
    	$path = explode('/', $path[1]);
    	$game = array('system' => $path[1], 'file' => rtrim(end($path)));

    	return $game;
    	
    }

    public function getInfos($game) {

    	if (is_array($game)) {
	    	$remote = getenv('RECALBOX_ROMS_PATH').'/'.$game['system'].'/gamelist.xml';
	    	$downloaded_images_path = getenv('RECALBOX_ROMS_PATH').'/'.$game['system'];
	    	// Je sais pas pourquoi ça fait ça maintenant, mais faut créer le fichier en amont...
	    	$fichier = \Storage::put('gamelists/'.$game['system'].'.xml', 1);
	    	$local = storage_path('app/public/gamelists/'.$game['system'].'.xml');

	    	// On télécharge. A chaque fois, pour avoir un fichier à jour. Plus simple :p
	    	\SSH::into('recalbox')->get($remote, $local);

	    	$xml = simplexml_load_file($local);
	    	$gameinfos = array();
	    	foreach ($xml->game as $gamexml) {
	    		// strpos car il peut arriver qu'on ait des sous-dossiers, or on a gardé que le nom de fichier
	    		// Pour l'instant on part du principe que toutes les infos recherchées existent... TODO : gérer les absences d'images et les infos supplémentaires si présentes
	    		if (strpos($gamexml->path, $game['file']) !== false) {
	    			$gameinfos['status'] = 'on';
	    			$gameinfos['name'] = (string)$gamexml->name;
	    			$gameinfos['image_path'] = self::getImage((string)$gamexml->image, $downloaded_images_path, $game['system']);
	    			$gameinfos['system'] = $game['system'];
	    		}
	    	}
    	} else {
   			$gameinfos['status'] = 'off';
    	}

    	return $gameinfos;
    	
    }        


    public function getImage($image_path, $downloaded_images_path, $system) {

    	// On retire le "./" du chemin
    	$image_path = substr($image_path, 2);
    	$remote = $downloaded_images_path.'/'.$image_path;
    	$fichier = \Storage::put(''.$image_path, 1);
    	$local = storage_path('app/public/'.$image_path);    	

    	\SSH::into('recalbox')->get($remote, $local);

    	return $image_path;

    }


}
