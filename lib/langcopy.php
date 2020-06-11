<?php

class langcopy {
   
   public function run () {
      
      // Config auslesen
      // Alle Artikel (ids) über db auslesen
      $addon = rex_addon::get('awtranslator');
      
      $sourceLang = $addon->getConfig('sourcelang');
      $targetLang = $addon->getConfig('targetlang');
      $clearAll = $addon->getConfig('clearall');
      
      $sql = rex_sql::factory();
      $sql->setQuery('SELECT id FROM '.rex::getTablePrefix().'article WHERE clang_id = '.$sourceLang);
      $allArticles = $sql->getArray();
      
      // wenn "alle löschen" gesetzt ist alle Slices löschen (db)
      if ($clearAll) {
         $sql = rex_sql::factory();
         $qry = 'DELETE FROM '.rex::getTablePrefix().'article_slice WHERE clang_id = '.$targetLang;
         $sql->setQuery($qry);
      }
      
      // Alle Artikel durchgehen
      foreach ($allArticles as $article) {
         $artId = $article['id'];
         self::copyArticle($artId, $sourceLang, $targetLang);
      }
      
   }
   
   private function copyArticle ($artId, $sourceLang, $targetLang) {
         $slices = rex_article_slice::getSlicesForArticle($artId,$sourceLang);
         $ctypes = [];
         foreach ($slices as $slice) {
//            print_r($slice->getValue('id').' - ');
            self::copySlice($slice->getValue('id'),$sourceLang, $targetLang);
            $ctypes[$slice->getValue('ctype')] = 1;
         }
         // Priority Wert reorganisieren
         foreach ($ctypes as $ctype_id=>$v) {
            rex_sql_util::organizePriorities(
               rex::getTable('article_slice'),
               'priority',
               'article_id='.$artId.' AND clang_id='.$targetLang.' AND ctype_id='.$ctype_id.' AND revision=0',
               'priority, updatedate DESC'
             );
            
         }
   }
   
   private function copySlice ($sliceId, $sourceLang, $targetLang) {
      $sql = rex_sql::factory();
      $qry = 'SELECT * FROM '.rex::getTablePrefix().'article_slice WHERE id = '.$sliceId;
      $sql->setQuery($qry);
      // slice lesen
      $slice = $sql->getArray()[0];
      $slice['clang_id'] = $targetLang;
      unset($slice['id']);
      
      // slice schreiben
      $newslice = rex_sql::factory();
      $newslice->setTable(rex::getTablePrefix().'article_slice');
      $newslice->setValues($slice);
      $newslice->insert();
      
   } 
   
}