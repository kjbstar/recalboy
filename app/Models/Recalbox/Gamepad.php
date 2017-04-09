<?php

namespace App\Models\Recalbox;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class Gamepad extends Model
{
    public static function getCodes($input_file) {

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
}
