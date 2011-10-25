<?php
App::uses('AppHelper', 'View/Helper');
App::uses('Folder', 'Utility');

/**
 * PartialHelper
 *
 * @copyright Copyright (C) 2010 saku.
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class PartialHelper extends AppHelper {
    const VERSION = '2.0';
    
    public $partialCache = 'partial';
    
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
            $keys = array_merge(array($this->_View->viewPath, $this->_View->action, $underscored, $name), array_keys($options), array_keys($data));
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
            
            //$partial = $this->_render($fullPath, array_merge($this->_View->viewVars, $data));
            // Cast版
            $partialView = $this->cast($this->_View, 'PartialView');
            $partial = $partialView->custom_render($fullPath, array_merge($this->_View->viewVars, $data));
            
            /*
            // castがだめだったら戻す
            // 暫定対応 PHP 5.3.2以上
            // Reflection版
            $reflMethod = new ReflectionMethod('View', '_render');
            $reflMethod->setAccessible(true);
            $partial = $reflMethod->invoke($this->_View, $fullPath, array_merge($this->_View->viewVars, $data));*/
            
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
     * クラスを強制的にキャストする
     * 
     * @access private
     * @author sakuragawa
     * @url http://php.net/manual/ja/language.types.type-juggling.php
     */
    function cast($oldObject, $newClassName) {
        if(class_exists($newClassName)) {
            $oldSerializedObject = serialize($oldObject);
            $oldObjectNameLength = strlen(get_class($oldObject));
            $subtringOffset = $oldObjectNameLength + strlen($oldObjectNameLength) + 6;
            $newSerializedObject = 'O:' . strlen($newClassName) . ':"' . $newClassName . '":';
            $newSerializedObject .= substr($oldSerializedObject, $subtringOffset);
            return unserialize($newSerializedObject);
        } else {
            return false;
        }
    }
    
    // これは動いた
    /*function cast($obj,$class_type){
        if(class_exists($class_type,true)){
                $obj = unserialize(preg_replace("/^O:[0-9]+:\"[^\"]+\":/i", "O:".strlen($class_type).":\"".$class_type."\":", serialize($obj)));
        }
        return $obj;
    }*/    
    
    /**
     * render
     * 
     * @access private
     * @author sakuragawa
     */
    /*private function _render($___viewFn, $___dataForView = array()) {
        if (empty($___dataForView)) {
            $___dataForView = $this->viewVars;
        }

        extract($___dataForView, EXTR_SKIP);
        ob_start();

        include $___viewFn;

        return ob_get_clean();
    }*/
}


/**
 * renderするためだけのダミークラス
 * 
 * @access public
 * @author sakuragawa
 */
class PartialView extends View{    
    public function custom_render($___viewFn, $___dataForView = array()) {
        return $this->_render($___viewFn, $___dataForView);
    }
}
