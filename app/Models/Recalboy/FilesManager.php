<?php

namespace App\Models\Recalboy;

use Illuminate\Database\Eloquent\Model;

class FilesManager extends Model
{

    private static $output;

    // Crap ! This function is not made by me, but a few days later, I dont remember where I got it.
    // I'm not the author of this simple scan function.
    public static function scan($dir){

        $files = array();

        // Is there actually such a folder/file?

        if(file_exists($dir)){
        
            foreach(scandir($dir) as $f) {
            
                if(!$f || $f[0] == '.') {
                    continue; // Ignore hidden files
                }

                if(is_dir($dir . '/' . $f)) {

                    // The path is a folder

                    $files[] = array(
                        "name" => $f,
                        "type" => "folder",
                        "path" => $dir . '/' . $f,
                        "items" => self::scan($dir . '/' . $f) // Recursively get the contents of the folder
                    );
                }
                
                else {

                    // It is a file

                    $files[] = array(
                        "name" => $f,
                        "type" => "file",
                        "path" => $dir . '/' . $f,
                        "size" => filesize($dir . '/' . $f), // Gets the size of this file
                        "date" => date("Y-m-d H:i:s", filemtime($dir . '/' . $f))
                    );
                }
            }
        
        }

        return $files;
    }


}
