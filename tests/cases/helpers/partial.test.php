<?php

App::import('Core', 'Controller', false);
App::import('View', 'Theme');

class PartialHelperMockController extends Controller {
	var $uses = null;
	var $name = 'Partial';
	var $action = 'render';

	var $params = array(
		'controller' => 'partial',
		'action' => 'render',
	);
	var $theme = 'test_theme';
}

class PartialHelperTestCase extends CakeTestCase {

	function startCase() {
		$this->viewPath = App::pluginPath('PartialHelper') . 'tests/test_app/views/';
		$this->viewPath = str_replace('/', DS, $this->viewPath);
		App::build(array('views' => array($this->viewPath)));

		$this->Controller = new PartialHelperMockController;
		$this->Controller->constructClasses();
		$this->Controller->startupProcess();

		$this->View = new View($this->Controller, false);
		$this->ThemeView = new ThemeView($this->Controller, false);

		$this->View->helpers[] = $this->ThemeView->helpers[] = 'PartialHelper.Partial';

		$this->_renderAs('test', $this->View);
		$this->_renderAs('test', $this->ThemeView);
	}

	function _registerView($view = 'View') {
		ClassRegistry::flush();
		$this->$view->hasRendered = false;
		ClassRegistry::addObject('view', $this->$view);
	}

	function _renderAs($element, $view = null) {
		if (null === $view) {
			$view =& ClassRegistry::getObject('view');
		}

		if (!$view) {
			$view = $this->View;
		}
		$view->set('element', $element);
	}

	function _turnCache($cache) {
		$view =& ClassRegistry::getObject('view');
		$view->set(compact('cache'));
	}

	function _cacheFile($cache) {
		$base = CACHE . 'views' . DS . 'partial_%s_test';
		return sprintf($base, $cache === true ? '' : $cache['key']);
	}

	function testBasic() {
		$this->_registerView();

		$this->assertEqual($this->View->render('render', false), 'proper');
	}

	function testTheme() {
		$this->_registerView('ThemeView');

		$this->assertEqual($this->ThemeView->render('render', false), 'proper theme');
	}

	function testCache() {
		$cache = true;
		$this->_registerView();
		$this->_turnCache($cache);

		$this->assertEqual($this->View->render('render', false), 'proper');
		$this->assertEqual(file_get_contents($this->_cacheFile($cache)), 'proper');
		unlink($this->_cacheFile($cache));

		$cache = array('key' => 'testkey', 'time' => '+1 day');
		$this->_registerView();
		$this->_turnCache($cache);

		$this->assertEqual($this->View->render('render', false), 'proper');
		$this->assertEqual(file_get_contents($this->_cacheFile($cache)), 'proper');
		unlink($this->_cacheFile($cache));

		$this->_turnCache(false);
	}

	function testError() {
		$this->_registerView();
		$this->_renderAs('invalid');

		$this->assertPattern('/^Not Found: .+$/', $this->View->render('render', false));
	}
}
