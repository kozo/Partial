<?php
App::uses('View', 'View');
App::uses('Controller', 'Controller');
App::uses('PartialHelper', 'View/Helper');

class PartialsController extends Controller {

    public $name = 'Partials';

    public $uses = null;
    
    function index(){
    }
}

class TestPartialHelper extends PartialHelper {
}

class PartialHelperTest extends CakeTestCase {
    public function setUp() {
        parent::setUp();
        
        $request = new CakeRequest('partials/index');
        //$this->Controller = new Controller($request);
        $this->PartialController = new PartialsController($request);
            
        App::build(array(
            'plugins' => array(APP . 'Test' . DS . 'test_app' . DS . 'Plugin' . DS),
            'View' => array(
                        APP . 'Test' . DS . 'test_app' . DS . 'View'. DS
                        )
                    ), true);
        //CakePlugin::loadAll();
        Configure::write('debug', 2);
        
        $this->View = new View($this->PartialController);
        $this->View->action = 'index';
        $this->Partial = new TestPartialHelper($this->View);
    }
    
    public function tearDown() {
        parent::tearDown();
    }
    
    public function testBasicRender() {
        $result = $this->Partial->render('basic');
        $expected = 'test';
        $this->assertEqual($expected, $result);
    }
    
    public function testViewVars(){
        $params = array(
            'name' => 'saku',
            'address' => 'fukuoka'
        );
        $result = $this->Partial->render('view_vars', $params);
        $this->assertPattern("/name:saku/", $result);
        $this->assertPattern("/address:fukuoka/", $result);
    }
    
    public function testHelperUseRender(){
        $this->View->helpers = array('Html');
        $this->View->loadHelpers();
        
        $result = $this->Partial->render('helper');
        $expected = '<a href="/add">create</a>';
        $this->assertEqual($expected, $result);
    }
    
    public function testMissing(){
        $result = $this->Partial->render('missing');
        $expected = 'Partial Not Found: Partials/_missing.ctp';
        $this->assertEqual($expected, $result);
    }
    
    public function testCache(){
        Cache::drop('partial');
        $cachePath = TMP . 'cache' . DS . 'partial' . DS;
        Cache::config('partial', array('engine'=>'File', 'path' => $cachePath));
        Cache::clear('partial');

        $result = $this->Partial->render('cache', array(), array('cache' => true));
        $expected = 'cache!!';
        $this->assertEquals($expected, $result);

        $name = 'partial__' . 'partials_index__cache_cache';
        $result = Cache::read($name, 'partial');
        $this->assertEquals($expected, $result);
    }
    
    public function testCacheViewVars(){
        Cache::drop('partial');
        $cachePath = TMP . 'cache' . DS . 'partial' . DS;
        Cache::config('partial', array('engine'=>'File', 'path' => $cachePath));
        Cache::clear('partial');

        $params = array(
            'name' => 'saku',
            'address' => 'fukuoka',
        );
        $result = $this->Partial->render('cache', $params, array('cache' => true));
        $expected = 'cache!!';
        $this->assertEquals($expected, $result);
        
        $name = 'partial__' . 'partials_index__cache_cache_name_address';
        $result = Cache::read($name, 'partial');
        $this->assertEquals($expected, $result);
    }
}