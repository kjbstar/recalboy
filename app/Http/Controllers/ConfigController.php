<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ConfigController extends BaseController
{


    public function index() {
		
		$config = File::get(base_path('.env'));
    	return view('config', ['config' => $config]);
    	
    }


    public function update(Request $request) {
		
		// C'est très vilain, mais je fais aucun contrôle. Bundle Forms retiré dans Lumen, et la flemme. Comme ça reste du local...

		// On fait un backup quand même, juste au cas où
		$config_old = File::get(base_path('.env'));
		$backup_filename = 'env-'.Carbon::now().'.txt';
		$backup_filename = preg_replace('/[[:space:]]+/', '-', $backup_filename);

		if (!File::exists(storage_path('app/public/backups'))) {
				File::makeDirectory(storage_path('app/public/backups'));
		}
		$cmd = shell_exec('echo "'.$config_old.'" > '.storage_path('app/public/backups/'.$backup_filename));

		$config = $request->input('config');
		$cmd = shell_exec('echo "'.trim($config).'" > '.base_path('.env'));

		return self::index();
    	
    }


    public function history() {
		
		if (File::exists(storage_path('app/public/backups'))) {
			$files = array();
			$backups = File::files(storage_path('app/public/backups'));
			foreach ($backups as $value) {
				$files[] = pathinfo($value);
			}
			return view('history', ['backups' => true, 'files' => $files]);
		} else {
	    	return view('history', ['backups' => false]);
		}
    	
    }

}
