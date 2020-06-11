<?php

class importer {
   public static function run ($file_temp) {
      
      $success = '';
      $error = '';
      $articles_processed = 0;
      $slice_values_processed = 0;
      
      $filetext = file_get_contents($file_temp);
      $articles = explode('<<<',$filetext);
      array_shift($articles);
//      aw_helper::out($articles);
      foreach ($articles as $art_text) {
         $ret = self::import_article($art_text);
         if (!$ret['err']) $articles_processed++;
         $slice_values_processed += $ret['rows'];
      }
      
      $success .= $articles_processed.' von '.count($articles).' Artikeln erfolgreich importiert.<br>'.PHP_EOL;
      $success .= $slice_values_processed.' Slicevalues aktualisiert.<br>';
      if ($slice_values_processed) {
         $success .= rex_delete_cache();         
      }
      if ($success != '') {
          echo rex_view::success($success);
      }
      if ($error != '') {
          echo rex_view::error($error);
      }
      
      
   }
   
   private static function import_article ($art_text) {
      
      $err = false;
      $rows = 0;
      
      $slices = explode('<<',$art_text);
      
      // 1. Element enthält die Artikelkonfig (Artikel-Id)
      $art_config = array_shift($slices);
      $artId = (int) self::getArticleId($art_config);
      
      $sql = rex_sql::factory();
      // $sql->debugsql = 1;
      
      foreach ($slices as $slice) {
         $sliceconfig = self::getSliceConfig($slice);
         $valid = self::validateConfig($sliceconfig,array('Slice-id','Language-id','Field'));
         if (!$valid) {            
            echo rex_view::error('Fehlerhafter Slice '.isset($sliceconfig['Slice-id']) ? $sliceconfig['Slice-id'] : '' .'in Artikel '.$artId);
            $err = true;
            continue;
         }
         $slicecontent = self::getSliceContent($slice);
         
         $sql->setTable(rex::getTablePrefix().'article_slice');         
         $sql->setValue($sliceconfig['Field'],$slicecontent);
         $sql->setWhere(array(
            'id'=>$sliceconfig['Slice-id'],
            'clang_id'=>$sliceconfig['Language-id'],
            'article_id'=>$artId)
         );
         $rows += $sql->update()->getRows();
         
      }
      return array('err'=>$err, 'rows'=>$rows);
      
   }
   
   
   private static function getSliceContent($slice_text) {
      $slice = preg_split("/\r\n|\r|\n/",$slice_text);
      $first_line = array_shift($slice);
      return trim(substr($slice_text, strlen($first_line)));
   }
   
   private static function validateConfig ($source,$validateFields) {
      $validate = true;
      foreach ($validateFields as $key) {
         if (!isset($source[$key])) $validate = false;
      }
      return $validate;      
   }
   
   /**
    * Liefert [[k1=>v1],[k2=>v2]...]
    * @param type $slice
    * @return type
    */
   
   private static function getSliceConfig ($slice) {
      $out = [];
      $slice_config = preg_split("/\r\n|\r|\n/",$slice);
      $slice_config = str_replace(' ','',$slice_config[0]);
      $slice_config = explode(';',$slice_config);
      foreach ($slice_config as $val) {
         list($k, $v) = explode(':',$val);
         $out[$k] = $v;
      }
      return $out;      
   }
   
   
   private static function getArticleId($art_config) {
      $art_config = str_replace(' ','',$art_config);
      $art_id = explode(';',$art_config);
      $art_id = explode(':',$art_id[0]);
      if ($art_id[0] != 'Artikel-Id') {
         die('Fehlerhafte Datei');
      }
      return $art_id[1];
   }
   
}