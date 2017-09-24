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


    public function cacheClear() {
        
        \Cache::flush();

		$data['message'] = 'Cache cleared !';
		return response()->json($data);
   
    }


    public function update(Request $request, $rollback = null) {
		
		// C'est très vilain, mais je fais aucun contrôle. Bundle Forms retiré dans Lumen, et la flemme. Comme ça reste du local...

		// On fait un backup quand même, juste au cas où
		$config_old = File::get(base_path('.env'));
		$backup_filename = 'config-'.Carbon::now().'.txt';
		$backup_filename = preg_replace('/[[:space:]]+/', '-', $backup_filename);

		if (!File::exists(storage_path('app/public/backups/config'))) {
				File::makeDirectory(storage_path('app/public/backups/config'), 0777, true);
		}
		$cmd = shell_exec('echo "'.$config_old.'" > '.storage_path('app/public/backups/config/'.$backup_filename));

		// Rollback ou pas ?
		if ($request->input('rollback')) {
			$config = File::get(storage_path('app/public/backups/config/'.$request->input('rollback')));
		} else {
			$config = $request->input('config');			
		}
		$cmd = shell_exec('echo "'.trim($config).'" > '.base_path('.env'));

		return self::index();
    	
    }


    public function history() {
		
		if (File::exists(storage_path('app/public/backups/config'))) {
			$files = array();
			$backups = File::files(storage_path('app/public/backups/config'));
			foreach ($backups as $value) {
				$files[] = pathinfo($value);
			}
			$files = array_reverse($files);
			return view('history', ['backups' => true, 'files' => $files]);
		} else {
	    	return view('history', ['backups' => false]);
		}
    	
    }


}
