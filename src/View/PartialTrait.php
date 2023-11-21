<?php
declare(strict_types=1);

namespace Partial\View;

use Cake\View\Exception\MissingElementException;

trait PartialTrait
{
    public string $partialCache = 'partial';

    /**
     * @param string $name
     * @param array $data
     * @param array $options
     * @return array|string
     * @throws \Cake\View\Exception\MissingElementException
     */
    public function partial(string $name, array $data = [], array $options = []): array|string
    {
        $file = $plugin = null;

        if (!isset($options['callbacks'])) {
            $options['callbacks'] = false;
        }

        if (isset($options['cache'])) {
            $contents = $this->_elementCache($name, $data, $options);
            if ($contents !== false) {
                return $contents;
            }
        }

        $file = $this->_getPartialFileName($name);
        if ($file) {
            return $this->_renderElement($file, $data, $options);
        }

        if (empty($options['ignoreMissing'])) {
            [$plugin, $name] = $this->pluginSplit($name, true);
            $name = str_replace('/', DS, $name);
            $file = $plugin . $this->templatePath . DS . '_' . $name . $this->_ext;
            throw new MissingElementException($file);
        }
    }

    /**
     * @param string $name
     * @return string|bool
     */
    protected function _getPartialFileName(string $name): string|bool
    {
        [$plugin, $name] = $this->pluginSplit($name);

        $paths = $this->_paths($plugin);

        // add slash
        $names = explode(DS, $name);
        $names[count($names) - 1] = '_' . $names[count($names) - 1];
        $name = implode(DS, $names);

        foreach ($paths as $path) {
            if (file_exists($path . $this->getTemplatePath() . DS . $name . $this->_ext)) {
                return $path . $this->getTemplatePath() . DS . $name . $this->_ext;
            }
        }

        return false;
    }
}
