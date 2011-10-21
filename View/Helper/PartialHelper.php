<?php
App::uses('AppHelper', 'View/Helper');

/**
 * PartialHelper 
 */
/**
 * PartialHelper  code license:
 *
 * @copyright Copyright (C) 2010 saku.
 * @since CakePHP(tm) v 1.3
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class PartialHelper extends AppHelper {
    const VERSION = '2.0';
    
    public $partialCache = 'default';
    
    function render($name, $data = array(), $options = array(), $loadHelpers = false) {
        $file = $plugin = $key = null;

        $plugin = $this->plugin;
        if (isset($options['plugin'])) {
            $plugin = Inflector::camelize($options['plugin']);
        }

        if (isset($options['cache'])) {
            $underscored = null;
            if ($plugin) {
                $underscored = Inflector::underscore($plugin);
            }
            $keys = array_merge(array($underscored, $name), array_keys($options), array_keys($data));
            $caching = array(
                'config' => $this->partialCache,
                'key' => implode('_', $keys)
                );
            if (is_array($options['cache'])) {
                $defaults = array(
                    'config' => $this->partialCache,
                    'key' => $caching['key']
                    );
                $caching = array_merge($defaults, $options['cache']);
            }
            $key = 'partial_' . $caching['key'];
            $contents = Cache::read($key, $caching['config']);
            if ($contents !== false) {
                return $contents;
            }
        }
        
        // 「_」をつける
        $buf = explode(DS, $name);
        $buf[count($buf)-1] = '_' . $buf[count($buf)-1];
        $name = implode(DS, $buf);

        // ファイルパス取得
        $fullPath = $this->_getPartialFileName($name, $plugin);

        if ($fullPath !== false) {
            if ($loadHelpers === true) {
                $this->_View->loadHelpers();
            }
            
            $partial = $this->_render($fullPath, array_merge($this->_View->viewVars, $data));
            
            if (isset($options['cache'])) {
                // Todo: 書き込み位置の変更
                Cache::write($key, $partial, $caching['config']);
            }
            return $partial;
        }
        $file = $this->_View->viewPath . DS . $name . ".ctp";

        if (Configure::read('debug') > 0) {
            return "Element Not Found: " . $file;
        }
    }
    
    /**
     * ファイル名を取得する
     * 
     * @access private
     * @author sakuragawa
     */
    private function _getPartialFileName($name, $plugin) {
        $paths = App::path('View', $plugin);
        $exts = $this->_getExtensions();
        
        foreach ($exts as $ext)
        {
            foreach ($paths as $path)
            {
                if (file_exists($path . $this->_View->viewPath . DS . $name . $ext)) {
                    return $path . $this->_View->viewPath . DS . $name . $ext;
                }
            }
        }
        return false;
    }
    
    
    /**
     * 拡張子の配列に対応
     * 
     * @access private
     * @author sakuragawa
     */
    private function _getExtensions() {
        $exts = array($this->ext);
        $exts = Set::filter($exts);
        if(empty($exts)){
            return array('.ctp');
        }
        $exts = $exts[0];
        
        if(!in_array('.ctp', $exts)) {
            array_push($exts, '.ctp');
        }
        
        return $exts;
    }
    
    
    /**
     * render
     * 
     * @access private
     * @author sakuragawa
     */
    private function _render($___viewFn, $___dataForView = array()) {
        if (empty($___dataForView)) {
            $___dataForView = $this->viewVars;
        }

        extract($___dataForView, EXTR_SKIP);
        ob_start();

        include $___viewFn;

        return ob_get_clean();
    }
}