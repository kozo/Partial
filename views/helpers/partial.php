<?php
/**
 * PartialHelper 
 */
/**
 * PartialHelper  code license:
 *
 * @copyright Copyright (C) 2010 saku All rights reserved.
 * @since CakePHP(tm) v 1.3
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class PartialHelper extends AppHelper {
    const VERSION = '1.1';
    
    function render($name,  $params = array(), $loadHelpers = false){
        $view =& ClassRegistry::getObject('view');

        $file = $plugin = $key = null;

        if (isset($params['cache'])) {
            $expires = '+1 day';

            if (is_array($params['cache'])) {
                $expires = $params['cache']['time'];
                $key = Inflector::slug($params['cache']['key']);
            } elseif ($params['cache'] !== true) {
                $expires = $params['cache'];
                $key = implode('_', array_keys($params));
            }

            if ($expires) {
                $cacheFile = 'partial_' . $key . '_' . Inflector::slug($name);
                $cache = cache('views' . DS . $cacheFile, null, $expires);
                
                if (is_string($cache)) {
                    return $cache;
                }
            }
        }
        
        $buf = explode(DS, $name);
        $buf[count($buf)-1] = '_' . $buf[count($buf)-1];
        $name = implode(DS, $buf);

        $controller = $this->params['controller'];
        foreach($view->__paths as $val){
            $path = $val . $controller . DS . $name . $view->ext;
            if (is_file($path)) {
                $params = array_merge_recursive($params, $view->loaded);
                $partial = $view->_render($path, array_merge($view->viewVars, $params), $loadHelpers);
                if (isset($params['cache']) && isset($cacheFile) && isset($expires)) {
                    cache('views' . DS . $cacheFile, $partial, $expires);
                }
                return $partial;
            }
        }

        if (Configure::read() > 0) {
            return "Not Found: " . $path;
        }
    }
}
