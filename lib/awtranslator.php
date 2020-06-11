<?php

class awtranslator {
    public static function getDir()
    {
        $dir = rex_path::addonData('awtranslator');
        rex_dir::create($dir);

        return $dir;
    }
   
}