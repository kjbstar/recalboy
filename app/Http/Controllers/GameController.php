<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;

class GameController extends BaseController
{

	private $output;
	private $core;

    public function check() {
		
    	$array_commandes = array(
    		"retroarch" => "ps aux | grep 'retroarch -L' | egrep -o ".getenv('RECALBOX_ROMS_PATH')."'/.*'", // Retroarch
    		"fba2x" => "ps aux | grep '/usr/bin/fba2x' | egrep -o ".getenv('RECALBOX_ROMS_PATH')."'/.*'", // FBA
    	);
   	
    	foreach ($array_commandes as $key => $commande) {
    		if (is_null($this->output) || ( is_array($this->output) && count($this->output) === 1 ) ) {
    			$this->output = self::findGame($key, $commande);
    		}
    	}
		
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
	    			$gameinfos['system_logo'] = self::getSystemImage($game['system']);
	    		}
	    	}

	    	$extras = self::getExtras($game['file']);
	    	if (!is_null($extras)) {
	    		$gameinfos['extras'] = $extras;
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


     public function getSystemImage($system) {

        $remote = '/recalbox/share/system/.emulationstation/themes/recalbox-multi/'.$system.'/data/logo.svg';
        $fichier = \Storage::put('systems/'.$system.'.svg', 1);
        $local = storage_path('app/public/systems/'.$system.'.svg');       

        \SSH::into('recalbox')->get($remote, $local);

        $system_logo = public_path('storage/systems/'.$system.'.svg');

        return $system_logo;

    }   


    public function findGame($key, $commande) {

		\SSH::run($commande, function($output)
		{	
			$game = self::getGame($output);
			$this->output = self::getInfos($game);
		});

		$this->output['core'] = $key;

		return $this->output;

    }


    public function getExtras($gamefile) {

    	// On gicle l'extension
    	$gamename = preg_replace('/\\.[^.\\s]{3,4}$/', '', $gamefile);

    	$path = base_path('public/assets/extras/'.$gamename);

    	// On cherche si on a un dossier de ce nom
    	if (File::exists($path)) {
    		$files = File::files($path);
    		// On change le chemin en mode public
    		foreach ($files as $key => $value) {
    			$files[$key] = strstr($value, "assets");
    		}
    		return $files;
    	} else {
    		$files = null;
    		return $files;
    	}

    }

}
