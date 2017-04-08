<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class DemoController extends BaseController
{

	private $esoff;
	private $startgame;
	private $arch;
	private $joystickCount;
	private $joystickGuid;
	private $joystickName;
	private $evtest;

    public function launch() {
		//Cache::flush();
		// On va chercher la gamelist d'un système
    	$array_systems = explode(',', getenv('DEMO_SYSTEMS'));
    	$rand_systems = array_rand($array_systems);
    	$system = $array_systems[$rand_systems];
	    $remote = getenv('RECALBOX_ROMS_PATH').'/'.$system;
	    $gamelist = self::getGamelist($remote, $system);

	    // On choisit un jeu au hasard
    	$xml = simplexml_load_file($gamelist);
    	$rand_xml = array_rand($xml->xpath('game'));
    	$game = $xml->game[$rand_xml];
    	$gamefile = substr((string)$game->path, 2);

    	// Et je baisse le son \o/
    	/*$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://'.getenv('RECALBOX_IP').':1337/audio/volume');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, '0');
		$result = curl_exec($ch);*/

		// Maintenant on va faire comme l'API, mais en vilain PHP
		// On récupère les settings de ES
		$emusettings = self::getEmuSettings();
		// On génère les params de gamepads connectés
		$emuLauncherGamePadsParams = self::genGamePads($emusettings);
		//die(var_dump($emuLauncherGamePadsParams.' GUID PLAYER ONE = '.$this->guidPlayerOne));

		// Chemin du jeu
    	$fullgamepath = $remote.'/'.$gamefile;
    	
    	// Commandes
    	//$commande_esoff = '/etc/init.d/S31emulationstation stop';
    	$commande_esoff = 'killall -9 emulationstation';
    	$commande_eson = '/etc/init.d/S31emulationstation start';
    	$commande_startgame = 'python /usr/lib/python2.7/site-packages/configgen/emulatorlauncher.pyc '.$emuLauncherGamePadsParams.' -rom "'.$fullgamepath.'" -system "'.$system.'"';
    	$commande_listeninputs = 'cd /tmp && evtest '.Cache::get('devicePathPlayerOne').' > inputs.log';

		\SSH::run($commande_esoff, function($esoff){ $this->esoff = $esoff; });
		sleep(1);
		\SSH::run($commande_startgame, function($startgame){ $this->startgame = $startgame; });

		//die(var_dump($commande_startgame.' ********** '.$this->output.' --- '.$this->output2));


		// J'ai besoin de récuperer la valeur "code" des boutons Hotkey et Start du player one
		$input_file = self::getEmuInputCfg();
		$codesPlayerone = self::getCodes($input_file);
    
    	// Maintenant on lance evtest
    	\SSH::run($commande_listeninputs, function($evtest){ $this->evtest = $evtest; });
    	// On met en cache son pid pour le tuer le moment venu
    	Cache::put('pidevtest', $this->evtest, 600);

    	// On a terminé ici, maintenant faudra écouter evtest...

    }
    



    public function getGamelist($remote, $system) {

    	$cached = Cache::remember('demo_gamelist_'.$system, 60, function() use ($remote, $system) {
		    $fichier = \Storage::put('gamelists/'.$system.'.xml', 1);
		    $local = storage_path('app/public/gamelists/'.$system.'.xml');
		    \SSH::into('recalbox')->get($remote.'/gamelist.xml', $local);
		    return $local;
    	});

    	return $cached;
    }


    public function getEmuSettings() {

    	$cached = Cache::remember('es_settings', 15, function() {
	        $remote = '/recalbox/share/system/.emulationstation/es_settings.cfg';
	        $fichier = \Storage::put('settings/es_settings.xml', 1);
	        $local = storage_path('app/public/settings/es_settings.xml');       
	        \SSH::into('recalbox')->get($remote, $local);
		    return $local;
    	});

        return $cached;

    }


    public function getEmuInputCfg() {

    	$cached = Cache::remember('es_input', 15, function() {
	        $remote = '/recalbox/share/system/.emulationstation/es_input.cfg';
	        $fichier = \Storage::put('settings/es_input.xml', 1);
	        $local = storage_path('app/public/settings/es_input.xml');       
	        \SSH::into('recalbox')->get($remote, $local);
		    return $local;
    	});

        return $cached;

    }

    public function getCodes($input_file) {

     	$cached = Cache::remember('playerone_codes', 15, function() use ($input_file) {
     		$guid = Cache::get('guidPlayerOne');
     		$xml = simplexml_load_file($input_file);
     		$controllers = $xml->xpath('inputConfig');
     		$codesPlayerone = array();
     		foreach ($controllers as $key => $value) {
     			$controller = (array) $value;
     			if ( $controller['@attributes']['deviceGUID'] == $guid ) {
     				foreach ($controller['input'] as $key => $value) {
     					$input = (array) $value;
     					$codesPlayerone[$input['@attributes']['name']] = $input['@attributes']['code'];
     				}
     			}
     		}
		    return $codesPlayerone;
    	});

        return $cached;   	

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
