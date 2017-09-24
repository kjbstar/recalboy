<?php

namespace App\Models\Recalbox;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;

class Configuration extends Model
{

    private static $output;

    public static function setValue($param, $value, $config = null) {

        if ($config === null) {
            $config = '/recalbox/share/system/recalbox.conf';
        }        

        if ($param == 'audio.volume') {
            self::setVolume($value);
        }

        \SSH::run('sed -i "s/\('.$param.' *= *\).*/\1'.$value.'/" /recalbox/share/system/recalbox.conf');
            
    }

    public static function getValue($param, $config = null) {

        if ($config === null) {
            $config = '/recalbox/share/system/recalbox.conf';
        }

        \SSH::run('cat '.$config.' | grep "^'.$param.'" | sed "s/^\(.*\)\('.$param.'=\)\(.*\)$/\3/"', function($line) {
            self::$output = $line;
        });

        return trim(self::$output);

    }    

    public static function setVolume($value) {

        \SSH::run('amixer set PCM '.$value.'%');
            
    }

    // Une fonction à part pour couvrir les cas les plus courants...
    public static function enableRetroarchNetworkCommands($param = null, $config = null) {

        if ($param === null) {
            $param = 'network_cmd_enable';
        }

        if (Input::has('path')) {
            $config = Input::get('path');
        } elseif ($config != null) {
            $config = $config;
        } else {
            $config = '/recalbox/share/system/configs/retroarch/retroarchcustom.cfg';
        }  

        try {
            
            // LECTURE
            \SSH::run('cat '.$config.' | grep "^'.$param.'" | sed "s/^\(.*\)\('.$param.' = \)\(.*\)$/\3/" | tr -d "\""', function($line) {
                self::$output = $line;
            });

            // Déjà activé ? Rien à faire.
            if (trim(self::$output) == 'true') {
                return 'RetroArch Network Commands are already activated. You can close this windows.';
            }

            // Pas activé ? Activons le !
            elseif (trim(self::$output) == 'false') {
                \SSH::run('sed -i "s/\('.$param.' *= *\).*/\1\"true\"/" '.$config);
                return 'RetroArch Network Commands have been activated ! You can close this windows.';
            }

            // Pas trouvé ? Ajoutons-le !
            else {
                \SSH::run("echo 'network_cmd_enable = \"true\"' >> ".$config);
                return 'RetroArch Network Commands have been added to config file ! You can close this windows.';
            }
       
        } catch (Exception $e) {
            return 'Impossible to connect to your Recalbox. Please check IP in Config is correct, and that your Recalbox is turned on and connected to the network.';
        }




    }     

}
