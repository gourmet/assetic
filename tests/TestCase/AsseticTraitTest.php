<?php

namespace Gourmet\Test\TestCase;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Gourmet\Assetic\AsseticTrait;

class AsseticTraitTest extends TestCase {

	private $__reset;

	public function setUp() {
		parent::setUp();

		$this->Assetic = $this->getObjectForTrait('\Gourmet\Assetic\AsseticTrait');

		$this->Assetic->setWebrootPath('/webroot/origin', 'origin');
		$this->Assetic->setWebrootPath('/webroot/target', 'target');

		$this->_configurePaths();
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->Assetic);
	}

	protected function _configurePaths() {
		$this->Assetic->setCssPath('assets' . DS . 'styles', 'origin');
		$this->Assetic->setCssPath('assets' . DS . 'styles', 'target');
		$this->Assetic->setImagePath('assets' . DS . 'images', 'origin');
		$this->Assetic->setImagePath('assets' . DS . 'images', 'target');
		$this->Assetic->setJsPath('assets' . DS . 'scripts', 'origin');
		$this->Assetic->setJsPath('assets' . DS . 'scripts', 'target');
	}

	public function testGetCollection() {
		$this->Assetic->cssCollection = $this->getMock('\Assetic\Asset\AssetCollection');
		$result = $this->Assetic->getCssCollection();
		$expected = $this->Assetic->cssCollection;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getCssCollection(false);
		$expected = '\Assetic\Asset\AssetCollection';
		$this->assertInstanceOf($expected, $result);
		$this->assertNotEquals($this->Assetic->cssCollection, $result);

		$this->Assetic->imageCollection = $this->getMock('\Assetic\Asset\AssetCollection');
		$result = $this->Assetic->getImageCollection();
		$expected = $this->Assetic->imageCollection;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getImageCollection(false);
		$expected = '\Assetic\Asset\AssetCollection';
		$this->assertInstanceOf($expected, $result);
		$this->assertNotEquals($this->Assetic->imageCollection, $result);

		$this->Assetic->jsCollection = $this->getMock('\Assetic\Asset\AssetCollection');
		$result = $this->Assetic->getJsCollection();
		$expected = $this->Assetic->jsCollection;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getJsCollection(false);
		$expected = '\Assetic\Asset\AssetCollection';
		$this->assertInstanceOf($expected, $result);
		$this->assertNotEquals($this->Assetic->jsCollection, $result);
	}

	public function testGetFactory() {
		$this->Assetic->cssFactory = $this->getMock('\Assetic\Factory\AssetFactory', null, array('/target/path'));
		$result = $this->Assetic->getCssFactory('/target/path');
		$expected = $this->Assetic->cssFactory;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getCssFactory('/target/path', false);
		$expected = '\Assetic\Factory\AssetFactory';
		$this->assertInstanceOf($expected, $result);
		$this->assertNotEquals($this->Assetic->cssFactory, $result);

		$this->Assetic->imageFactory = $this->getMock('\Assetic\Factory\AssetFactory', null, array('/target/path'));
		$result = $this->Assetic->getImageFactory('/target/path');
		$expected = $this->Assetic->imageFactory;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getImageFactory('/target/path', false);
		$expected = '\Assetic\Factory\AssetFactory';
		$this->assertInstanceOf($expected, $result);
		$this->assertNotEquals($this->Assetic->imageFactory, $result);

		$this->Assetic->jsFactory = $this->getMock('\Assetic\Factory\AssetFactory', null, array('/target/path'));
		$result = $this->Assetic->getJsFactory('/target/path');
		$expected = $this->Assetic->jsFactory;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getJsFactory('/target/path', false);
		$expected = '\Assetic\Factory\AssetFactory';
		$this->assertInstanceOf($expected, $result);
		$this->assertNotEquals($this->Assetic->jsFactory, $result);
	}

	public function testGetFilterManager() {
		$this->Assetic->cssFilterManager = $this->getMock('\Assetic\FilterManager');
		$result = $this->Assetic->getCssFilterManager();
		$expected = $this->Assetic->cssFilterManager;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getCssFilterManager(false);
		$expected = '\Assetic\FilterManager';
		$this->assertInstanceOf($expected, $result);
		$this->assertNotEquals($this->Assetic->cssFilterManager, $result);

		$this->Assetic->imageFilterManager = $this->getMock('\Assetic\FilterManager');
		$result = $this->Assetic->getImageFilterManager();
		$expected = $this->Assetic->imageFilterManager;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getImageFilterManager(false);
		$expected = '\Assetic\FilterManager';
		$this->assertInstanceOf($expected, $result);
		$this->assertNotEquals($this->Assetic->imageFilterManager, $result);

		$this->Assetic->jsFilterManager = $this->getMock('\Assetic\FilterManager');
		$result = $this->Assetic->getJsFilterManager();
		$expected = $this->Assetic->jsFilterManager;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getJsFilterManager(false);
		$expected = '\Assetic\FilterManager';
		$this->assertInstanceOf($expected, $result);
		$this->assertNotEquals($this->Assetic->jsFilterManager, $result);
	}

	public function testGetManager() {
		$this->Assetic->cssManager = $this->getMock('\Assetic\AssetManager');
		$result = $this->Assetic->getCssManager();
		$expected = $this->Assetic->cssManager;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getCssManager(false);
		$expected = '\Assetic\AssetManager';
		$this->assertInstanceOf($expected, $result);
		$this->assertNotEquals($this->Assetic->cssManager, $result);

		$result = $this->Assetic->getCssManager(false, $this->getMock('\Assetic\Factory\AssetFactory', null, array('/target/path')));
		$expected = '\Assetic\Factory\LazyAssetManager';
		$this->assertInstanceOf($expected, $result);

		$this->Assetic->imageManager = $this->getMock('\Assetic\AssetManager');
		$result = $this->Assetic->getImageManager();
		$expected = $this->Assetic->imageManager;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getImageManager(false);
		$expected = '\Assetic\AssetManager';
		$this->assertInstanceOf($expected, $result);
		$this->assertNotEquals($this->Assetic->imageManager, $result);

		$result = $this->Assetic->getImageManager(false, $this->getMock('\Assetic\Factory\AssetFactory', null, array('/target/path')));
		$expected = '\Assetic\Factory\LazyAssetManager';
		$this->assertInstanceOf($expected, $result);

		$this->Assetic->jsManager = $this->getMock('\Assetic\AssetManager');
		$result = $this->Assetic->getJsManager();
		$expected = $this->Assetic->jsManager;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getJsManager(false);
		$expected = '\Assetic\AssetManager';
		$this->assertInstanceOf($expected, $result);
		$this->assertNotEquals($this->Assetic->jsManager, $result);

		$result = $this->Assetic->getJsManager(false, $this->getMock('\Assetic\Factory\AssetFactory', null, array('/target/path')));
		$expected = '\Assetic\Factory\LazyAssetManager';
		$this->assertInstanceOf($expected, $result);
	}

	public function testGetPath() {
		$result = $this->Assetic->getWebrootPath();
		$expected = DS . 'webroot' . DS . 'origin' . DS;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getWebrootPath('target');
		$expected = DS . 'webroot' . DS . 'target' . DS;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getCssPath();
		$expected = $this->Assetic->getWebrootPath() . 'assets' . DS . 'styles' . DS;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getCssPath('target');
		$expected = $this->Assetic->getWebrootPath('target') . 'assets' . DS . 'styles' . DS;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getImagePath();
		$expected = $this->Assetic->getWebrootPath() . 'assets' . DS . 'images' . DS;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getImagePath('target');
		$expected = $this->Assetic->getWebrootPath('target') . 'assets' . DS . 'images' . DS;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getJsPath();
		$expected = $this->Assetic->getWebrootPath() . 'assets' . DS . 'scripts' . DS;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getJsPath('target');
		$expected = $this->Assetic->getWebrootPath('target') . 'assets' . DS . 'scripts' . DS;
		$this->assertEquals($expected, $result);
	}

	public function testGetWriter() {
		$this->Assetic->cssWriter = $this->getMock('\Assetic\AssetWriter', null, ['/target/path']);
		$result = $this->Assetic->getCssWriter('/target/path');
		$expected = $this->Assetic->cssWriter;
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->getCssWriter('/target/path', false);
		$expected = '\Assetic\AssetWriter';
		$this->assertInstanceOf($expected, $result);
		$this->assertNotEquals($this->Assetic->cssWriter, $result);
	}

	public function testGetFilePath() {
		$file = 'assetic.css';
		$expected = $this->Assetic->getCssPath() . $file;
		$result = $this->Assetic->getFilePath($file);
		$this->assertEquals($expected, $result);

		$file = 'assetic.css';
		$expected = $this->Assetic->getCssPath('target') . $file;
		$result = $this->Assetic->getFilePath($file, ['src' => 'target']);
		$this->assertEquals($expected, $result);

		$file = 'assetic.js';
		$expected = $this->Assetic->getJsPath('target') . $file;
		$result = $this->Assetic->getFilePath($file, ['src' => 'target']);
		$this->assertEquals($expected, $result);
	}

	public function getFileType() {
		$expected = 'css';
		$result = $this->Assetic->getFileType('assetic.css');
		$this->assertEquals($expected, $result);

		$expected = 'css';
		$result = $this->Assetic->getFileType('assetic.less');
		$this->assertEquals($expected, $result);

		$expected = 'css';
		$result = $this->Assetic->getFileType('assetic.sass');
		$this->assertEquals($expected, $result);

		$expected = 'css';
		$result = $this->Assetic->getFileType('assetic.sass');
		$this->assertEquals($expected, $result);

		$expected = 'js';
		$result = $this->Assetic->getFileType('assetic.js');
		$this->assertEquals($expected, $result);

		$expected = 'js';
		$result = $this->Assetic->getFileType('assetic.coffee');
		$this->assertEquals($expected, $result);

		$expected = 'image';
		$result = $this->Assetic->getFileType('assetic.jpg');
		$this->assertEquals($expected, $result);

		$expected = 'image';
		$result = $this->Assetic->getFileType('assetic.jpeg');
		$this->assertEquals($expected, $result);

		$expected = 'image';
		$result = $this->Assetic->getFileType('assetic.png');
		$this->assertEquals($expected, $result);

		$expected = 'image';
		$result = $this->Assetic->getFileType('assetic.gif');
		$this->assertEquals($expected, $result);
	}

	public function testNormalizeExtension() {
		$file = 'assetic';

		$this->Assetic->setWebrootPath(null);
		$this->Assetic->setWebrootPath(dirname(__DIR__) . DS . 'test_app' . DS . 'webroot' . DS . 'origin');
		$this->_configurePaths();

		$result = $this->Assetic->normalizeCssExtension($file);
		$expected = $file . '.css';
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->normalizeCssExtension($file . '-less', ['less']);
		$expected = $file . '-less.less';
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->normalizeCssExtension($file . '-less', ['sass', 'lessphp']);
		$expected = $file . '-less.less';
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->normalizeCssExtension($file . '-scss', ['sass', 'lessphp']);
		$expected = $file . '-scss.scss';
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->normalizeJsExtension($file);
		$expected = $file . '.js';
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->normalizeJsExtension($file, ['min']);
		$expected = $file . '.js';
		$this->assertEquals($expected, $result);

		$result = $this->Assetic->normalizeJsExtension($file, ['coffee']);
		$expected = $file . '.coffee';
		$this->assertEquals($expected, $result);
	}

	public function testNormalizePath() {
		$path = 'css';
		$result = $this->Assetic->normalizePath($path);
		$expected = $path . DS;
		$this->assertEquals($expected, $result);

		$path = 'css/';
		$result = $this->Assetic->normalizePath($path);
		$expected = $path;
		$this->assertEquals($expected, $result);
	}

	public function testResolveFilters() {
		$filters = [];
		$actual = $this->Assetic->resolveCssFilters($filters);
		$expected = $filters;
		$this->assertEquals($expected, $actual);

		$this->Assetic->cssFilterManager = $this->getMock('\Assetic\Asset\FilterManager', array('has', 'get'), array(), 'FilterManager');

		$this->Assetic->cssFilterManager->expects($this->once())->method('has')->with('less')->will($this->returnValue(true));
		$this->Assetic->cssFilterManager->expects($this->once())->method('get')->with('less')->will($this->returnValue('lessObject'));
		$filters = ['less'];
		$actual = $this->Assetic->resolveCssFilters($filters);
		$expected = ['less' => 'lessObject'];
		$this->assertEquals($expected, $actual);
	}

/**
 * @expectedException \Psr\Log\InvalidArgumentException
 */
	public function testResolveFiltersThrowsException() {
		$this->Assetic->resolveCssFilters(['less']);
	}

	public function testSetPath() {
		$this->Assetic->setCssPath('origin' . DS . 'path');

		$expected = $this->Assetic->getWebrootPath() . 'origin' . DS . 'path' . DS;
		$result = $this->Assetic->getCssPath();
		$this->assertEquals($result, $expected);

		$this->Assetic->setCssPath('target' . DS . 'path', 'target');

		$expected = $this->Assetic->getWebrootPath('target') . 'target' . DS . 'path' . DS;
		$result = $this->Assetic->getCssPath('target');
		$this->assertEquals($result, $expected);

		$this->Assetic->setImagePath('origin' . DS . 'path');

		$expected = $this->Assetic->getWebrootPath() . 'origin' . DS . 'path' . DS;
		$result = $this->Assetic->getImagePath();
		$this->assertEquals($result, $expected);

		$this->Assetic->setImagePath('target' . DS . 'path', 'target');

		$expected = $this->Assetic->getWebrootPath('target') . 'target' . DS . 'path' . DS;
		$result = $this->Assetic->getImagePath('target');
		$this->assertEquals($result, $expected);

		$this->Assetic->setJsPath('origin' . DS . 'path');

		$expected = $this->Assetic->getWebrootPath() . 'origin' . DS . 'path' . DS;
		$result = $this->Assetic->getJsPath();
		$this->assertEquals($result, $expected);

		$this->Assetic->setJsPath('target' . DS . 'path', 'target');

		$expected = $this->Assetic->getWebrootPath('target') . 'target' . DS . 'path' . DS;
		$result = $this->Assetic->getJsPath('target');
		$this->assertEquals($result, $expected);

		$this->Assetic->setWebrootPath(null);
		$result = $this->Assetic->getWebrootPath();
		$expected = null;
		$this->assertEquals($expected, $result);

		$this->Assetic->setWebrootPath(DS . 'webroot' . DS . 'origin');
		$this->_configurePaths();
		$result = DS . 'webroot' . DS . 'origin' . DS;
		$expected = $this->Assetic->getWebrootPath();
		$this->assertEquals($expected, $result);
	}

}
