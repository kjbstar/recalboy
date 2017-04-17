<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ConfigController extends BaseController
{


    public function index() {
		
		$config = File::get(base_path('.env'));
    	return view('config', ['config' => $config]);
    	
    }


    public function update(Request $request) {
		
		// C'est très vilain, mais je fais aucun contrôle. Bundle Forms retiré dans Lumen, et la flemme. Comme ça reste du local...
		$config = $request->input('config');
		$cmd = shell_exec('echo "'.$config.'" > '.base_path('.env'));
		//die(var_dump($cmd));
		//File::put(base_path('.env'), $config);
		return self::index();
    	
    }


}
