<?php

class langcopy {

    private static $source_version = 0;
    private static $target_version = 0;

    public function run() {

        // Config auslesen
        // Alle Artikel (ids) über db auslesen
        $addon = rex_addon::get('awtranslator');

        $sourceLang = $addon->getConfig('sourcelang');
        $targetLang = $addon->getConfig('targetlang');
        $clearAll = $addon->getConfig('clearall');

        if (rex_plugin::get('structure', 'version')->isAvailable()) {
            self::$source_version = $addon->getConfig('version_source');
            self::$target_version = $addon->getConfig('version_target');
        }

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'article WHERE clang_id = ' . $sourceLang);
        $allArticles = $sql->getArray();

        // wenn "alle löschen" gesetzt ist alle Slices löschen (db)
        if ($clearAll) {
            $sql = rex_sql::factory();
            $qry = 'DELETE FROM ' . rex::getTable('article_slice') . ' WHERE clang_id = :clang_id AND revision = :revision';
            $sql->setQuery($qry, ['clang_id' => $targetLang, 'revision' => self::$target_version]);
        }

        // Alle Artikel durchgehen
        foreach ($allArticles as $article) {
            $artId = $article['id'];
            self::copyArticle($artId, $sourceLang, $targetLang);
        }
    }

    private function copyArticle($artId, $sourceLang, $targetLang) {
        $slices = rex_article_slice::getSlicesForArticle($artId, $sourceLang, self::$source_version);
        $ctypes = [];
        foreach ($slices as $slice) {
//            print_r($slice->getValue('id').' - ');
            self::copySlice($slice->getValue('id'), $sourceLang, $targetLang);
            $ctypes[$slice->getValue('ctype')] = 1;
        }
        // Priority Wert reorganisieren
        foreach ($ctypes as $ctype_id => $v) {
            rex_sql_util::organizePriorities(
                    rex::getTable('article_slice'),
                    'priority',
                    'article_id=' . $artId . ' AND clang_id=' . $targetLang . ' AND ctype_id=' . $ctype_id . ' AND revision='.self::$target_version,
                    'priority, updatedate DESC'
            );
        }
    }

    private function copySlice($sliceId, $sourceLang, $targetLang) {
        $sql = rex_sql::factory();
        $qry = 'SELECT * FROM ' . rex::getTable('article_slice') . ' WHERE id = :id AND revision = :revision';
        $sql->setQuery($qry,['id'=>$sliceId,'revision'=>self::$source_version]);
        // slice lesen
        $slice = $sql->getArray()[0];
        $slice['clang_id'] = $targetLang;
        $slice['revision'] = self::$target_version;
        unset($slice['id']);

        // slice schreiben
        $newslice = rex_sql::factory();
        $newslice->setTable(rex::getTable('article_slice'));
        $newslice->setValues($slice);
        $newslice->insert();
    }

}
