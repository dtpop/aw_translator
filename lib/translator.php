<?php

class translator {

    public function export() {
        
        $addon = rex_addon::get('awtranslator');

        if (trim($addon->getConfig('xmlmode'), '|') == '1') {
            // xml mode ...

            xmlexport::export();
        } else {

            $addon = rex_addon::get('awtranslator');
            $langId = $addon->getConfig('language');

            $sql = rex_sql::factory();
            $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'article WHERE clang_id = ' . $langId);
            $allArticles = $sql->getArray();

            // Config einlesen für jedes Modul

            $sql = rex_sql::factory();
            $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'module ORDER BY name');

            $Modules = $sql->getArray();

            $modulesConfig = [];
            foreach ($Modules as $Module) {
                $modulesConfig[$Module['id']] = explode(',', $addon->getConfig('module' . $Module['id']));
            }


            // Textmode ...

            $out = chr(239) . chr(187) . chr(191); // BOM
            $characters = 0;

            foreach ($allArticles as $artId) {
                $art_title = '<<< Artikel-Id: ' . $artId['id'] . '; Link: ' . rex_yrewrite::getFullUrlByArticleId($artId['id'], $langId) . PHP_EOL . str_repeat('=', 80) . "\r\n\r\n";
                $art_out = '';

                $slices = rex_article_slice::getSlicesForArticle($artId['id'], $langId);

                foreach ($slices as $slice) {
                    $module_id = $slice->getModuleId();
                    if (!is_array($modulesConfig[$module_id]) || $modulesConfig[$module_id][0] == '')
                        continue;
                    // Für jeden in der Konfiguration definierten value des Slices den Wert auslesen
                    foreach ($modulesConfig[$module_id] as $slice_value) {
                        //                dump($slice->getValue(intval($slice_value)));
                        $art_out .= '<< Slice-id: ' . $slice->getValue('id') . '; Language-id: ' . $langId . '; Field: value' . $slice_value . '; Ctype: ' . $slice->getValue('ctype') . "\r\n\r\n";
                        $art_out .= $slice->getValue(intval($slice_value)) . "\r\n\r\n";
                        $characters += strlen($slice->getValue(intval($slice_value)));
                    }
                }
                if ($art_out) {
                    $out .= $art_title;
                    $out .= $art_out;
                }
            }

            $out .= "\r\n" . 'Anzahl Zeichen: ' . $characters;
            /*
              echo '<pre>';
              print_r($out);
              echo '</pre>';
             */



            $filename = 'text_to_translate_' . rex_clang::get($langId)->getCode() . '.txt';

            ob_end_clean();
            ob_start();
            header("Content-Type: text/plain");
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header("Content-Length: " . strlen($out));
            echo $out;
            ob_end_flush();
            exit;
        }
    }

}

/*
 * Importer:
 * exploden mit <<<
 *    exploden mit <<
 *    << Zeile auswerten -> sql update
 */