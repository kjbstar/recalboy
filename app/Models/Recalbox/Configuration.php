<?php

namespace App\Models\Recalbox;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{

    private $output;

    public static function setValue($param, $value) {

        \SSH::run('sed -i "s/\('.$param.' *= *\).*/\1'.$value.'/" /recalbox/share/system/recalbox.conf', function($output){
            $this->output = $output;
            return $this->output; 
        });
            

    } 
}
