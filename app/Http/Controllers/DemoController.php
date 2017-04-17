<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use App\Models\Recalbox\Files as Files;
use App\Models\Recalbox\Gamepad as Gamepad;
use App\Models\Recalbox\Configuration as Config;

class DemoController extends BaseController
{

	private $startgame;
	private $arch;
	private $joystickCount;
	private $joystickGuid;
	private $joystickName;
	private $inputs;

    public function launch() {

		// On va chercher la gamelist d'un système
    	$array_systems = explode(',', getenv('DEMO_SYSTEMS'));
    	$rand_systems = array_rand($array_systems);
    	$system = $array_systems[$rand_systems];
	    $remote = getenv('RECALBOX_ROMS_PATH').'/'.$system;
	    $gamelist = Files::getGamelist($remote, $system);

	    // On choisit un jeu au hasard
    	$xml = simplexml_load_file($gamelist);
    	$rand_xml = array_rand($xml->xpath('game'));
    	$game = $xml->game[$rand_xml];
    	$gamefile = substr((string)$game->path, 2);

    	// Et je baisse le son \o/
    	$audio = Config::setValue('audio.volume', getenv('DEMO_VOLDOWN'));

		// Maintenant on va faire comme l'API, mais en vilain PHP
		// On récupère les settings de ES
		$emusettings = Files::getEmuSettings();
		// On génère les params de gamepads connectés
		$emuLauncherGamePadsParams = self::genGamePads($emusettings);

		// Chemin du jeu
    	$fullgamepath = $remote.'/'.$gamefile;
    	
    	// Commandes
    	//$commande_esoff = '/etc/init.d/S31emulationstation stop';
    	$commande_esoff = 'killall -9 emulationstation';
    	$commande_startgame = 'python /usr/lib/python2.7/site-packages/configgen/emulatorlauncher.pyc '.$emuLauncherGamePadsParams.' -rom "'.$fullgamepath.'" -system "'.$system.'"';
    	$commande_listeninputs = 'cd /tmp && evtest '.Cache::get('devicePathPlayerOne').' > inputs.log &';

    	// On kill ES
		\SSH::run($commande_esoff);
		sleep(1);

		// On lance le jeu !
		\SSH::run($commande_startgame);

		// J'ai besoin de récuperer la valeur "code" des boutons Hotkey et Start du player one
		$input_file = Files::getEmuInputCfg();
		$codesPlayerone = Gamepad::getCodes($input_file);
    
    	// Maintenant on lance evtest
    	\SSH::run($commande_listeninputs);
    	// Dommage, la commande ne renvoit pas les outputs des process lancés en background, donc pas de pid, pas de gestion fine, on fera un killall...
    	//Cache::put('pidevtest', $this->evtest, 600);

    	// TODO vérifier si un jeu a bien été lancé !!
    	return response()->json(array('demo' => true));

    	// On a terminé ici, maintenant faudra écouter evtest...

    }
 

 	public function checkPlayer() {

 		$codes = Cache::get('playerone_codes');

 		// Si au moins 3 boutons principaux (pas une direction) ont été appuyés, alors on considère que le joueur a pris la main, il faut arrêter le mode démo 		
 		$boutons = array(
 				'a' => (int) $codes['a'],
 				'b' => (int) $codes['b'],
 				'x' => (int) $codes['x'],
 				'y' => (int) $codes['y'],
 				'select' => (int) $codes['select'],
 				'start' => (int) $codes['start'],
 				'hotkey' => (int) $codes['hotkey']
 			);

 		// On a déjà filtré pour ne garder que les boutons, pas les directions ou autre. Mais l'array $boutons permettra éventuellement des features futures.
 		\SSH::run('tail -100 /tmp/inputs.log | grep "^Event:" | grep "EV_KEY" | grep "value 1" | sed "s/^.* \([-]\?[0-9]\+\) (.*), value 1/\1/"', function($output){
 			$this->inputs = $output;
 		});

 		$output_tmp = preg_split('/\s+/', trim($this->inputs));
 		//$output_tmp = preg_split('/ +/', trim('314 314 314 314 314 314 315 304 304 307 305 305 307 307 308 308 308 305 304 304 304 304 305 305 305 305 305 305 305 316 '));

 		// On remet bien en tout en integers (plus moche, mais plus rapide)
 		foreach ($output_tmp as $key => $value) {
 			$output[] = (int) $value;
 		}

 		// On compare les 2 arrays
 		$resultat = array_intersect($boutons, $output);

 		// Question => Est-ce que le joueur a quitté le jeu via la manette ? Si oui, il faut relancer ES
 		// J'ai un doute sur la pertinence de l'emplacement de cette fonction ici, et son champ d'action (quid si à la manette je save puis j'appuie sur start ?...). On verra si ca pose problème ou pas.
 		if (array_key_exists('start', $resultat) && array_key_exists('hotkey', $resultat)) {
 			return response()->json(array('demo' => 'gamepad_quit'));
 		}

 		// Si je trouve hotkey sans start, et que le joueur n'a pas pris la main, alors on passe direct au jeu suivant (simuler un mode "random play")
 		if (array_key_exists('hotkey', $resultat)) {
 			return response()->json(array('demo' => 'gamepad_skip'));
 		} 		 

 		// On check si on a bien au moins 3 boutons qui ont été appuyés. Si oui, on arrête le mode démo !
 		if (count($output) >= 3) {
 			// Et je monte le son :)
	 		Config::setValue('audio.volume', getenv('DEMO_VOLUP'));			
 			return response()->json(array('demo' => false));
 		} else {
 			return response()->json(array('demo' => true));
 		}

 	}


    public function off() {

    	//$commande = '/etc/init.d/S31emulationstation start';
    	// Pour une raison qui m'échappe, la commande s'arrête au bout de 3/4 secondes et ES ne termine pas son démarrage (?!)
    	// Du coup je prend la commande complète, en 2 fois, au lieu du script S31... Là ca marche (!?)
    	// System language : pas besoin de la commande python pour ca, je vais direct aller le lire
    	$language = Config::getValue('system.language');
    	$commande = 'HOME=/recalbox/share/system LC_ALL="'.$language.'.UTF-8" SDL_VIDEO_GL_DRIVER=/usr/lib/libGLESv2.so SDL_NOMOUSE=1 /usr/bin/emulationstation; [ -f /tmp/shutdown.please ] && (shutdown -h now);[ -f /tmp/reboot.please ] && (shutdown -r now) &';

    	// On tue evtest
		self::kill();

		// On remet le son
		Config::setValue('audio.volume', getenv('DEMO_VOLUP'));			

    	// On redémarre ES
		\SSH::run($commande);

    	return response()->json(array('demo' => false));

    }


    public function kill() {

    	//$commande = 'kill '.Cache::get('pidevtest');
    	$commande = 'killall -9 evtest';
		
    	// On kill ce qu'on à killer (evtest...)
		\SSH::run($commande);

    }


    public function genGamePads($emusettings) {

    	// On met tout ça en cache pour pas se retaper tout ça à chaque nouveau jeu lancé : peu de chances que les gamepads changent en quelques minutes !!
    	$cached = Cache::remember('gamepads', 15, function() use ($emusettings) {

		    $xml = simplexml_load_file($emusettings);

		    // On prépare l'array des gamepads (pas nécéssaire selon moi, mais bon...)
		    $emuSettingPlayers = array(
		    		array('name' => 'DEFAULT', 'guid' => ''),
		    		array('name' => 'DEFAULT', 'guid' => ''),
		    		array('name' => 'DEFAULT', 'guid' => ''),
		    		array('name' => 'DEFAULT', 'guid' => ''),
		    	);

	    	foreach ($xml->string as $key => $value) {
	    		switch ($value['name'][0]) {
	    			case 'INPUT P1NAME':
	    				$emuSettingPlayers[0]['name'] = (string)$value['value'][0];
	    				break;
	    			case 'INPUT P1GUID':
	    				$emuSettingPlayers[0]['guid'] = (string)$value['value'][0];
	    				break;
	    			case 'INPUT P2NAME':
	    				$emuSettingPlayers[1]['name'] = (string)$value['value'][0];
	    				break;
	    			case 'INPUT P2GUID':
	    				$emuSettingPlayers[1]['guid'] = (string)$value['value'][0];
	    				break;
	    			case 'INPUT P3NAME':
	    				$emuSettingPlayers[2]['name'] = (string)$value['value'][0];
	    				break;
	    			case 'INPUT P3GUID':
	    				$emuSettingPlayers[2]['guid'] = (string)$value['value'][0];
	    				break;
	    			case 'INPUT P4NAME':
	    				$emuSettingPlayers[3]['name'] = (string)$value['value'][0];
	    				break;
	    			case 'INPUT P4GUID':
	    				$emuSettingPlayers[3]['guid'] = (string)$value['value'][0];
	    				break;    				    				    				    				
	    		}
	    	}


	    	// On vérifie l'archi
	    	\SSH::run('uname -m', function($arch){ $this->arch = $arch; });
			if (strpos($this->arch, 'arm') !== false) {
			    $arch = 'arm';
			} else {
				$arch = 'x64';
			}   	

	    	// Maintenant les gamepads disponibles
	    	$availableGamepads = array();

	    	\SSH::run('/usr/recalbox-api/libs/bin/joystickCount-linux-'.$arch, function($joystickCount){ $this->joystickCount = $joystickCount; });

	    	for ($i=0; $i < $this->joystickCount; $i++) { 
	    		\SSH::run('/usr/recalbox-api/libs/bin/joystickGuid-linux-'.$arch.' '.$i, function($joystickGuid){ $this->joystickGuid = $joystickGuid; });
	    		\SSH::run('/usr/recalbox-api/libs/bin/joystickName-linux-'.$arch.' '.$i, function($joystickName){ $this->joystickName = $joystickName; });
	    		
	    		$availableGamepads[] = array(
	    				'index' => $i,
	    				'guid' => $this->joystickGuid,
	    				'name' => $this->joystickName,
	    				'devicePath' => '/dev/input/event'.$i
	    			);

	    	}

	    	// Et enfin on construit la partie de la commande qu'on donnera à emulatorlancher pour les gamepads
	    	$emuLauncherGamePadsParams = ' ';
	    	for ($it=1; $it <= count($availableGamepads); $it++) { 
	    		$emuLauncherGamePadsParams .= '-p'.$it.'index '.$availableGamepads[$it-1]['index'].' -p'.$it.'guid '.$availableGamepads[$it-1]['guid'].' -p'.$it.'name "'.$availableGamepads[$it-1]['name'].'" -p'.$it.'devicepath '.$availableGamepads[$it-1]['devicePath'].' ';
	    		// Besoin d'avoir ces données sous la main.
	    		if ($it === 1) {
	    			Cache::put('guidPlayerOne', $availableGamepads[$it-1]['guid'], 15);
	    			Cache::put('devicePathPlayerOne', $availableGamepads[$it-1]['devicePath'], 15);
	    		}
	    	}
	    	return $emuLauncherGamePadsParams;

		});

		return $cached;

    } 


}
