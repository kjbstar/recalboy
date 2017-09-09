<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Recalbox\Files as Files;
use App\Models\Recalboy\FilesManager as FilesManager;

class BackupController extends BaseController
{

    public function backupsManager() {
		
    	return view('backups.index');
    	
    }

    public function gamesSavesManager() {
        
        return view('backups.games_saves', ['backups' => true]);
        
    }


    public function listGamesSaves() {
		
		$path = base_path('public/storage/backups/saves');

    	$files = FilesManager::scan($path);

    	$files = array(
			"name" => 'saves',
			"type" => "folder",
			"path" => $path,
			"items" => $files    		
    		);

		$resp = json_encode($files);
    	
		return response()->json($files);

    }


    public function getGameSave($method, $system, $game) {

        $game = urldecode($game);
        $now = Carbon::now();
        $now = $now->format('Y-m-d H').'h';
        $check_path = '/recalbox/share/saves/'.$system.'/'.escapeshellarg($game).'.*';

        // Est-ce qu'on trouve un ou des fichiers lié(s) au jeu ?
        \SSH::run('ls '.$check_path, function($output)
        {
            $cherche = strstr($output, 'No such file or directory');
            if ($cherche == true) {
                $output = 'Pas de sauvegarde trouvée !';
                exit($output);
            } 
            $this->output = $output;
        });

        // Oui on en trouve au moins 1, on sépare les différents chemins
        $fichiers = explode('/recalbox', $this->output);
        unset($fichiers[0]);
        foreach ($fichiers as $key => $value) {
            $liste[] = '/recalbox'.rtrim($value);
        }


        // On check si on a déjà backupé le(s) même(s) fichier(s)
        foreach ($liste as $key => $value) {
            $fichier = explode('/recalbox/share/saves/'.$system.'/', $value);
            $local = storage_path('app/public/backups/saves/'.$system.'/'.$game.'/'.$now.'/'.$fichier[1]);

            if ( file_exists($local) ) {
                $md5 = Files::compareMd5($local, $value);
                // Si les MD5 sont identiques, c'est le même fichier, donc pas besoin de le récupérer de nouveau !
                if ($md5['local'] === $md5['remote']) {
                    unset($liste[$key]);
                }
            }
        }

        // On récupère ce qui reste !
        foreach ($liste as $key => $value) {
            $ext = explode('.', $value); // [1] = extension
            $res[] = Files::getSave($value, $system, $game, $ext[1], $now);
        }

        

        return 'Recalboy synchronisé avec Recalbox !';


    }

    // https://medium.com/@paulredmond/how-to-submit-json-post-requests-to-lumen-666257fe8280
    public function restoreFile(\Illuminate\Http\Request $request) {
        
        $local = $request->get('path');
        $type = $request->get('type');

        // Restaurer une sauvegarde de jeu
        if ($type == 'game_save') {

            $exploded = explode('/', $local);
            $gamedata = array_reverse($exploded);
            $file = $gamedata[0];
            $game = $gamedata[2];
            $system = $gamedata[3];

            // On peut recréer le chemin côté Recalbox
            $remote = '/recalbox/share/saves/'.$system.'/'.$file;

            // Est-ce que le fichier existe déjà ?
            $check = Files::remoteCheck($remote);

            // Le fichier existe.
            if ($check == true) {   
                // Est-ce que c'est le même fichier qu'on tente de restaurer ?
                $md5 = Files::compareMd5($local, $remote);
                // Si les MD5 sont identiques, c'est le même fichier, donc pas besoin de restaurer !
                if ($md5['local'] === $md5['remote']) {
                    $data['message'] = 'Cancelled : the exact same file exist on your Recalbox.';
                    return response()->json($data);
                } else {
                // Sinon, les fichiers sont différents, on va donc d'abord effacer celui sur Recalbox
                    \SSH::into('recalbox')->delete($remote);    
                }                
            }

            // Le fichier n'existe pas (ou plus), on peut maintenant procéder à la restauration.
            \SSH::into('recalbox')->put($local, $remote);

            $data['message'] = $game. '\'s save file has successfully been restored to your Recalbox !';
            return response()->json($data);

        }

    
    }



}
