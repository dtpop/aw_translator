<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of xmlexport
 *
 * @author wolfgang
 */
class xmlexport {

    private static $instance = null;
    private static $moduleConfig = [];
    private static $allArticles = [];
    private static $langId = 0;
    private static $dom;

    private function __construct() {

        $addon = rex_addon::get('awtranslator');
        self::$langId = $addon->getConfig('language');

        // Config einlesen für jedes Modul

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'module ORDER BY name');

        $Modules = $sql->getArray();

        $modulesConfig = [];
        foreach ($Modules as $Module) {
            if ($addon->getConfig('module' . $Module['id'])) {
                if (strpos($addon->getConfig('module' . $Module['id']),'{') !== false) {
                    self::$moduleConfig[$Module['id']] = json_decode($addon->getConfig('module' . $Module['id']),true);
                } else {
                    self::$moduleConfig[$Module['id']] = explode(',', $addon->getConfig('module' . $Module['id']));
                }
            }
        }
        
        if ($addon->getConfig('articles_to_translate')) {
            self::$allArticles = explode(',',$addon->getConfig('articles_to_translate'));
            self::$allArticles = array_unique(self::$allArticles);
        } else {
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'article WHERE clang_id = ' . self::$langId);
            self::$allArticles = $sql->getArray();
            self::$allArticles = array_column(self::$allArticles, 'id');
        }

    }

    // The object is created from within the class itself
    // only if the class has no instance.
    public static function export() {
        $htmlout = '';
        if (self::$instance == null) {
            self::$instance = new xmlexport();
        }
        
        $addon = rex_addon::get('awtranslator');
        
        self::$dom = new DOMDocument();
        self::$dom->encoding = 'utf-8';
        self::$dom->xmlVersion = '1.0';
        self::$dom->formatOutput = true;

        $xml_file_name = 'text_to_translate_'.rex_clang::get(self::$langId)->getCode().'.xml';
        $export_time = new DOMAttr('ExportTime', date('Y-m-d h:i:s'));
        $export_lang_code = new DOMAttr('TargetLangCode', rex_clang::get(self::$langId)->getCode());
        $export_lang_id = new DOMAttr('TargetLangId', self::$langId);
        
        $root = self::$dom->createElement('RedaxoContent');
        $root->setAttributeNode($export_time);
        $root->setAttributeNode($export_lang_code);
        $root->setAttributeNode($export_lang_id);        
        
        dump($addon->getConfig('version'));

        // alle Artikel ...
        foreach (self::$allArticles as $artId) {
            $slices = rex_article_slice::getSlicesForArticle($artId, self::$langId, $addon->getConfig('version'));
            $rex_article = rex_article::get($artId,self::$langId);
            
            $page_node = self::$dom->createElement('page');
            $xml_article_id = new DOMAttr('articleId', $artId);
            $xml_article_url = new DOMAttr('articleUrl', trim(rex::getServer(),'/').rex_getUrl($artId,self::$langId));
            $page_node->setAttributeNode($xml_article_id);
            $page_node->setAttributeNode($xml_article_url);
            
            
            foreach (preg_split('/\R/',$addon->getConfig('additional_meta_fields')) as $meta_info_field) {
                if (!$meta_info_field) {
                    continue;
                }
                $meta_info_text = $rex_article->getValue($meta_info_field);
                $meta_info_node = self::$dom->createElement('metaInfo');
                $xml_field_name = new DOMAttr('fieldName', $meta_info_field);
                $xml_node_value = self::$dom->createElement('Value', $rex_article->getValue('yrewrite_description'));
                $meta_info_node->appendChild($xml_node_value);
                $meta_info_node->setAttributeNode($xml_field_name);
                $page_node->appendChild($meta_info_node);
            }
            

            // Slices, die nicht übersetzt werden sollen entfernen
            foreach ($slices as $k => $slice) {
                if (!array_key_exists($slice->getModuleId(), self::$moduleConfig)) {
                    unset($slices[$k]);
                }
            }
            
            if ($slices) {
                $page_node = self::export_slices($slices,$page_node);
            }
            $root->appendChild($page_node);
        }
        
        
        self::$dom->appendChild($root);
        
        
        $htmlout = self::$dom->saveHTML();
        $htmlout = html_entity_decode($htmlout,ENT_HTML5,'UTF-8');
        
        self::send_export($htmlout);
        
        
//        file_put_contents($xml_file_name,$htmlout);
        
//        echo rex_view::success('Export wurde erstellt.');
        
    }
    
    private static function send_export ($htmlout) {
        
            $filename = 'text_to_translate_' . rex_clang::get(self::$langId)->getCode() . '.xml';

            ob_end_clean();
            ob_start();
            header("Content-Type: text/plain");
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header("Content-Length: " . strlen($htmlout));
            echo $htmlout;
            ob_end_flush();
            exit;
        
        
    }

    private static function export_slices($slices, $page_node) {
        foreach ($slices as $slice) {
            $module_config = self::$moduleConfig[$slice->getModuleId()];
            
            $slice_node = self::$dom->createElement('slice');
            $xml_slice_id = new DOMAttr('sliceId', $slice->getId());
            $slice_node->setAttributeNode($xml_slice_id);
            
//            dump($module_config); // array:1 [1 => array:2 [0 => "name",1 => "area"]] für mblock und mform
                                  // array:1 [0 => "1"] für normale Module
            
            foreach ($module_config as $val_id=>$module_value) {
                $xml_slice_value = self::$dom->createElement('sliceValue');
                if (is_array($module_value)) {
                    // json Inhalt, entweder als Array (mblock) oder Einzelelement (mform)
                    
                    $val_content = rex_var::toArray($slice->getValue((int) $val_id));
                    $xml_slice_value_id = new DOMAttr('sliceValueId', $val_id);
                    $xml_slice_value->setAttributeNode($xml_slice_value_id);
                    
                    $write_slice = false;
                    
                    if (!$module_value) {
                        // Normaler Value
                        
//                        dump($val_id);
                        $xml_slice_value_type = new DOMAttr('sliceValueType', 'rex');
                        $xml_slice_value->setAttributeNode($xml_slice_value_type);
                        $val_content = $slice->getValue((int) $val_id);
                        
//                        dump($val_content);
                        $val_content = aw_xliff::encode_xliff($val_content);
//                        dump($val_content);
                        
                        
                        $xml_node_value = self::$dom->createElement('Value', $val_content);
                        
                        
                        $xml_slice_value->appendChild($xml_node_value);
//                        $xml_slice_value->appendChild($xml_slice_value_element);
                        $write_slice = true;
                        
                        
                        
                    } elseif (is_array($val_content) && isset($val_content[0]) && is_array($val_content[0])) {
                        // mblock                        

                        $xml_slice_value_type = new DOMAttr('sliceValueType', 'mblock');
                        $xml_slice_value->setAttributeNode($xml_slice_value_type);
                        
                        foreach ($val_content as $element_id => $mblock_element) {
                            $xml_slice_value_element = self::$dom->createElement('sliceValueElement');
                            $xml_slice_value_element_id = new DOMAttr('sliceValueElementId', $element_id);
                            $xml_slice_value_element->setAttributeNode($xml_slice_value_element_id);
                            
                            foreach ($module_value as $mblock_label) {
                                $mblock_value = $mblock_element[$mblock_label];
                                
                                $mblock_value = aw_xliff::encode_xliff($mblock_value);                                
                                
                                $xml_node_value = self::$dom->createElement('Value', $mblock_value);
                                $xml_slice_value_element_key = new DOMAttr('sliceValueElementKey', $mblock_label);
                                $xml_node_value->setAttributeNode($xml_slice_value_element_key);
                                
                                $xml_slice_value_element->appendChild($xml_node_value);                                
                                $write_slice = true;
                            }
                            $xml_slice_value->appendChild($xml_slice_value_element);
                            
                        }
                    } elseif (is_array($module_value)) {
                        // mform Elemente
                        $val_content = rex_var::toArray($slice->getValue((int) $val_id));
                        $xml_slice_value_type = new DOMAttr('sliceValueType', 'mform');
                        $xml_slice_value->setAttributeNode($xml_slice_value_type);
                        foreach ($module_value as $mblock_label) {
                            if ($val_content[$mblock_label]) {
                                $xml_slice_value_element = self::$dom->createElement('sliceValueElement');
                                $xml_slice_value_element_key = new DOMAttr('sliceValueElementKey', $mblock_label);
                                $xml_slice_value_element->setAttributeNode($xml_slice_value_element_key);
                                
                                $outstring = $val_content[$mblock_label];
                                $outstring = aw_xliff::encode_xliff($outstring);
                                
                                $xml_node_value = self::$dom->createElement('Value', $outstring);
                                $xml_slice_value_element->appendChild($xml_node_value);                                
                                $xml_slice_value->appendChild($xml_slice_value_element);
                                $write_slice = true;
                            }
                            
                        } 
                    } else {
                        
                    }
                    if ($write_slice) {
                        $slice_node->appendChild($xml_slice_value);
                    }
                }
//                dump($module_value);
            }
            $page_node->appendChild($slice_node);
        }
        return $page_node;
    }
    
    

}
