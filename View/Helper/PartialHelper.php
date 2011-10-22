<?php
App::uses('AppHelper', 'View/Helper');
App::uses('Folder', 'Utility');

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
    
    public $partialCache = 'partial';
    
    public function __get($name) {
        if(isset($this->_View->{$name})){
            return $this->_View->{$name};
        }
        
        return parent::__get($name);
    }
    
    public function __call($method, $params) {
        // Todo : element等Viewのメソッドが動かない
        // Todo : このやり方まずい気がする・・・
        call_user_func_array(array($this->_View, $method), $params);
    }
    
    function render($name, $data = array(), $options = array(), $loadHelpers = true) {
        $file = $plugin = $key = null;
        // キャッシュの設定(フォルダがない場合は新規作成)
        $cachePath = TMP . 'cache' . DS . 'partial' . DS;
        $obj = new Folder($cachePath, true, 0777);
        Cache::config($this->partialCache, array('engine'=>'File', 'path' => $cachePath));

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
                Cache::write($key, $partial, $caching['config']);
            }
            return $partial;
        }
        $file = $this->_View->viewPath . DS . $name . ".ctp";

        if (Configure::read('debug') > 0) {
            return "Partial Not Found: " . $file;
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
        $exts = array($this->_View->ext);
        if(empty($exts)){
            return array('.ctp');
        }
        
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
