<?php

namespace App\Models\Recalbox;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{

    private static $output;

    public static function setValue($param, $value, $config = null) {

        if ($config === null) {
            $config = '/recalbox/share/system/recalbox.conf';
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

}
