<?php
App::uses('AppHelper', 'View/Helper');

/**
 * PartialHelper 
 */
/**
 * PartialHelper  code license:
 *
 * @copyright Copyright (C) 2010-2011 saku.
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class PartialHelper extends AppHelper {
    const VERSION = '2.0';
    
    function render($name,  $params = array(), $loadHelpers = false){

        // @todo:キャッシュ機構がまるで未実装
        
        $plugin = null;
        if (isset($params['plugin'])) {
            $plugin = Inflector::camelize($params['plugin']);
        }
        $paths = App::path('View', $plugin);
        $filename = $name;
        if ( !empty($paths) ) {
            foreach ($paths as $path) {
                $filename = $path.$this->_View->viewPath.DS.'_'.$name.$this->_View->ext;
                if (is_file($filename) ) {
                    return $this->_View->render($filename, false);
                }
            }
        } 

        if (Configure::read() > 0) {
            return "Not Found: " . $filename;
        }
    }
}