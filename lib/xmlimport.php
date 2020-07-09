<?php

/*
 * TODO - mform und rex handling umbauen wie mblock
 */

/**
 * Description of xmlimport
 *
 * @author wolfgang
 */
class xmlimport {
    //put your code here

    private static $instance = null;
    private static $article_id = 0;
    private static $slice_id = 0;
    private static $slice_value_type = ''; // rex, mform, mblock
    private static $slice_value_id = 0; // REX_VALUE[...]
    private static $target_lang_id = 0;
    private static $dom;
    private static $slicesql;
    
    private function __construct() {
        
    }    
    
    public static function import($filename = '') {
        if (self::$instance == null) {
            self::$instance = new xmlimport();
        }
        self::$slicesql = rex_sql::factory()->setTable(rex::getTable('article_slice'));
        self::$dom = new DOMDocument();
        libxml_use_internal_errors(true);
//        $filetext = file_get_contents($filename);
//        $doc->loadHTML($filetext);
//        $doc->loadXML($filetext);
        self::$dom->load($filename);
        self::getDOMNode(self::$dom);
        
        
    }
    
    private static function getDOMNode(DOMNode $domNode) {
        foreach ($domNode->childNodes as $node) {
            if ($node->nodeName == 'page') {
                self::$article_id = $node->getAttribute('articleId');                
            }
            if ($node->nodeName == 'slice') {
                self::$slice_id = $node->getAttribute('sliceId');
            }
            if ($node->nodeName == 'metaInfo') {
            }
            if ($node->nodeName == 'RedaxoContent') {
                self::$target_lang_id = $node->getAttribute('TargetLangId');
            }
            if ($node->nodeName == 'sliceValue') {
                self::$slice_value_type = $node->getAttribute('sliceValueType');
                self::$slice_value_id = $node->getAttribute('sliceValueId');
                if (self::$slice_value_type == 'mblock') {
                    self::save_mblock_value($node);
                }
                if (self::$slice_value_type == 'mform') {
                    self::save_mform_value($node);
                }                
                if (self::$slice_value_type == 'rex') {
                    self::save_rex_value($node);
                }
                continue;
                // todo: rex und mform auf sliceValue-Ebene (hier) einbauen
            }
//            dump($node);
            if ($node->nodeName == 'metaInfo') {
                self::save_meta_info($node);
                continue;
            }
            
            
            if ($node->hasChildNodes()) {
                self::getDOMNode($node);
            }
        }
    }
    
    
    private static function save_meta_info ($node) {
        $field_name = $node->getAttribute('fieldName');
        foreach ($node->childNodes as $child) {
            if ($child->nodeName == 'Value') {
                $sql = rex_sql::factory();
                $sql->setTable(rex::getTable('article'));
                $sql->setWhere('id = :id AND clang_id = :clang_id',['id'=>self::$article_id,'clang_id'=>self::$target_lang_id]);
                $sql->setValue($field_name,self::get_inner_html($child));
                $sql->update();
            }
        }
    }
    
    private static function save_rex_value ($node) {
        
        foreach ($node->childNodes as $child) {
            if ($child->nodeName == 'Value') {
                // Neuen Wert einsetzen
                self::save_slice_value(self::get_inner_html($child));
            }
        }
        
    }
    
    private static function save_slice_value ($value) {
        self::$slicesql->setTable(rex::getTable('article_slice'));
        self::$slicesql->setWhere('id = :id AND article_id = :article_id AND clang_id = :clang_id',['id'=>self::$slice_id,'article_id'=>self::$article_id,'clang_id'=>self::$target_lang_id]);
        self::$slicesql->setValue('value'.self::$slice_value_id,$value);
        self::$slicesql->update();        
    }
    
    private static function save_mform_value ($node) {
        // Vorhandenen Slice einlesen
        $slice = rex_article_slice::getArticleSliceById(self::$slice_id, self::$target_lang_id);
        $slice_value = rex_var::toArray($slice->getValue((int) self::$slice_value_id));
        // JSON Wert auslesen
        foreach ($node->childNodes as $child) {
            if ($child->nodeName == 'sliceValueElement') {
                // key
                $value_element_key = $child->getAttribute('sliceValueElementKey');
                foreach ($child->childNodes as $child_val) {
                    if ($child_val->nodeName == 'Value') {
                        // Neuen Wert einsetzen
                        if (isset($slice_value[$value_element_key])) {
                            $slice_value[$value_element_key] = self::get_inner_html($child_val);
                        }
                    }
                }
            }
        }
        self::save_slice_value(json_encode($slice_value));
    }
    
    private static function save_mblock_value ($node) {
        $slice = rex_article_slice::getArticleSliceById(self::$slice_id, self::$target_lang_id);
        $slice_value = rex_var::toArray($slice->getValue((int) self::$slice_value_id));
        
        foreach ($node->childNodes as $child) {
            if ($child->nodeName == 'sliceValueElement') {
                $slice_index = (int) $child->getAttribute('sliceValueElementId');
                foreach ($child->childNodes as $child_val) {
                    if ($child_val->nodeName == 'Value') {
                        $value_element_key = $child_val->getAttribute('sliceValueElementKey');
                        // Neuen Wert einsetzen
                        $slice_value[$slice_index][$value_element_key] = self::get_inner_html($child_val);
                    }                    
                }
            }
        }
        self::save_slice_value(json_encode($slice_value));
        
    }
    
    
    private static function get_inner_html($node) {
        return trim(self::DOMinnerHTML($node));
    }    
    

    /**
     * 
     * @param DOMNode $element
     * @return type
     */
    private static function DOMinnerHTML(DOMNode $element) { 
        $innerHTML = ""; 
        $children  = $element->childNodes;

        foreach ($children as $child) 
        { 
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return $innerHTML; 
    }     
    
}

