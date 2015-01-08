<?php
/*App::uses('View', 'View');
App::uses('Folder', 'Utility');*/

trait PartialTrait{
    public $partialCache = 'partial';

    function partial($name, $data = array(), $options = array(), $loadHelpers = true) {
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
            $keys = array_merge(array($this->viewPath, $this->action, $underscored, $name), array_keys($options), array_keys($data));
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
                $this->loadHelpers();
            }

            $partial = $this->_render($fullPath, array_merge($this->viewVars, $data));

            if (isset($options['cache'])) {
                Cache::write($key, $partial, $caching['config']);
            }
            return $partial;
        }
        $file = $this->viewPath . DS . $name . ".ctp";

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
                if (file_exists($path . $this->viewPath . DS . $name . $ext)) {
                    return $path . $this->viewPath . DS . $name . $ext;
                }
            }
        }
        return false;
    }
}
