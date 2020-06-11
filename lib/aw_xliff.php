<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of aw_xliff
 *
 * @author wolfgang
 */
class aw_xliff {
    
    private static $instance = null;
    private static $doc;
    private static $html;
    private static $ignore_tags = ['body','html','xml'];
    private static $lev = 1;
    
    private function __construct($html) {
        /*
        libxml_use_internal_errors(true);
        self::$doc = new DOMDocument();
        self::$doc->loadHTML(
            '<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>');        
         * 
         */
    }    
    
    public static function encode_xliff ($html = '') {
        /*
        if (self::$instance == null) {
            self::$instance = new aw_xliff($html);
        }
         * 
         */
        $html = str_replace('<br>','<br />',$html);
//        $html = str_replace('&nbsp;','xxxxxx',$html);
        $html = htmlspecialchars_decode($html);
        
/*        
        $html = preg_replace('|(</.*?>)|','###ept id="1"###$1###/ept###',$html); // Endtags
        $html = preg_replace('|(<[^/].*?>)|','###bpt id="1"###$1###/bpt###',$html); // Anfangstags
        
        $html = str_replace(['###bpt id="1"###','###/bpt###','###ept id="1"###','###/ept###',],['<bpt id="1">','</bpt>','<ept id="1">','</ept>'],$html);;
  */      
        
//        dump($html);
        
        /*
        self::domnode(self::$doc);
        
        dump(self::$doc->saveHTML());
        
//        dump(self::$out);
        
//        dump(self::$doc);
        self::$instance = null;
//        dump(self::$out);
        return 'xxx';
         * 
         */
        return $html;
    }
    
    /*
    
    private static function domnode ($doc) {
        dump($doc->nodeValue);
        
        foreach ($doc->childNodes as $node) {
            if (!in_array($node->nodeName,self::$ignore_tags)) {
                self::$lev ++;
                $node->nodeValue = 'huhu';
//                self::$out .= '<bpt id="'.self::$lev.'" ctype="'.$node->nodeName.'">&lt;'.$node->nodeName.'&gt;</bpt>';
//                self::$out .= $node->nodeValue;
//                self::$out .= '<ept id="'.self::$lev.'">&lt;/'.$node->nodeName.'&gt;</ept>';
//                dump($node->nodeName.':'.$node->nodeValue);
//                self::$lev --;
            }
            if($node->hasChildNodes()) {
                self::domnode($node);
            }
        }    
        
        
    }
     */
    
    
    
}
