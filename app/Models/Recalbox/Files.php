<?php

namespace App\Models\Recalbox;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class Files extends Model
{
    public static function getGamelist($remote, $system) {

        $cached = Cache::remember('gamelist_'.$system, 60, function() use ($remote, $system) {
            $fichier = \Storage::put('gamelists/'.$system.'.xml', 1);
            $local = storage_path('app/public/gamelists/'.$system.'.xml');
            \SSH::into('recalbox')->get($remote.'/gamelist.xml', $local);
            return $local;
        });

        return $cached;
    }


    public static function getEmuSettings() {

        $cached = Cache::remember('es_settings', 15, function() {
            $remote = '/recalbox/share/system/.emulationstation/es_settings.cfg';
            $fichier = \Storage::put('settings/es_settings.xml', 1);
            $local = storage_path('app/public/settings/es_settings.xml');       
            \SSH::into('recalbox')->get($remote, $local);
            return $local;
        });

        return $cached;

    }


    public static function getEmuInputCfg() {

        $cached = Cache::remember('es_input', 15, function() {
            $remote = '/recalbox/share/system/.emulationstation/es_input.cfg';
            $fichier = \Storage::put('settings/es_input.xml', 1);
            $local = storage_path('app/public/settings/es_input.xml');       
            \SSH::into('recalbox')->get($remote, $local);
            return $local;
        });

        return $cached;

    }


    public static function getGameImage($image_path, $downloaded_images_path, $system) {

        $cached = Cache::remember('GameImage_'.$image_path, 604800, function() use ($image_path, $downloaded_images_path, $system) {
            // On retire le "./" du chemin
            $image_path = substr($image_path, 2);
            $remote = $downloaded_images_path.'/'.$image_path;
            $fichier = \Storage::put(''.$image_path, 1);
            $local = storage_path('app/public/'.$image_path);       
            \SSH::into('recalbox')->get($remote, $local);
            if ($image_path == 'assets/img/recalboy.png') {
                return $image_path;
            } else {
                return 'storage/'.$image_path;
            }
        });

        return $cached;

    }


    public static function getSystemImage($system) {

        $cached = Cache::remember('SystemImage_'.$system, 604800, function() use ($system) {
            $remote = '/recalbox/share/system/.emulationstation/themes/recalbox-multi/'.$system.'/data/logo.svg';
            $fichier = \Storage::put('systems/'.$system.'.svg', 1);
            $local = storage_path('app/public/systems/'.$system.'.svg');       
            \SSH::into('recalbox')->get($remote, $local);
            $system_logo = public_path('storage/systems/'.$system.'.svg');
            return $system_logo;
        });

        return $cached;

    }    

}
