<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use App\Models\Recalbox\Files as Files;

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
		
		if (is_null($this->output) || !array_key_exists('status', $this->output)) {
			$this->output = array("status" => "off");
		}

        self::renderHtml($this->output);

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

            $downloaded_images_path = getenv('RECALBOX_ROMS_PATH').'/'.$game['system'];
            $remote = getenv('RECALBOX_ROMS_PATH').'/'.$game['system'];

            $local = Files::getGamelist($remote, $game['system']);

	    	$xml = simplexml_load_file($local);
	    	$gameinfos = array();
	    	foreach ($xml->game as $gamexml) {
	    		// strpos car il peut arriver qu'on ait des sous-dossiers, or on a gardé que le nom de fichier
	    		// Pour l'instant on part du principe que toutes les infos recherchées existent... TODO : gérer les absences d'images et les infos supplémentaires si présentes
	    		if (strpos($gamexml->path, $game['file']) !== false) {
	    			$gameinfos['status'] = 'on';
	    			$gameinfos['name'] = (string)$gamexml->name;
	    			$gameinfos['image_path'] = Files::getGameImage((string)$gamexml->image, $downloaded_images_path, $game['system']);
                    $gameinfos['system'] = $game['system'];
	    			$gameinfos['system_logo'] = Files::getSystemImage($game['system']);
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


    public function renderHtml($output) {

        if ($output['status'] == 'on') {
            switch (getenv('THEME')) {
                case 'micro':
                    $html = '<div class="grid-2 has-gutter"><div class="mas"><img src="/storage/'.$output['image_path'].'"></div><div class="mas"><div class="grid has-gutter"><img src="'.$output['system_logo'].'"></div></div><a href="#actions" class="material-icons" id="gotoactions">arrow_downward</a></div>';
                    break;
                default:
                    $html = '<div class="grid-2 has-gutter"><div class="mas"><img src="/storage/'.$output['image_path'].'"></div><div class="mas"><div class="grid has-gutter"><img src="'.$output['system_logo'].'"></div><table><tr><td>GAME</td><td>'.$output['name'].'</td></tr><tr><td>SYSTEM</td><td>'.$output['system'].'</td></tr></table></div></div>';
                    break;
            }
            $this->output['html'] = $html;
        }

    }    

}
