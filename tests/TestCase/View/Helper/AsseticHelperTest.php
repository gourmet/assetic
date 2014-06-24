<?php

namespace Gourmet\Test\TestCase\View\Helper;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Gourmet\Assetic\View\Helper\AsseticHelper;

class AsseticHelperTest extends TestCase {

	public $Assetic;

	private $__resetAppConfig;

	private $__resetAsseticConfig;

	public function setUp() {
		parent::setUp();

		$this->__resetAppConfig = Configure::read('App');
		Configure::write('App.www_root', dirname(dirname(dirname(__DIR__))) . DS . 'test_app' . DS . 'webroot' . DS);

		$this->__resetAsseticConfig = Configure::consume('Assetic');
		// Configure::write('Assetic', ['cssGroups' => ['main' => ['cake.group']]]);

		$view = $this->getMock('Cake\View\View', array('append'));

		$this->Assetic = new AsseticHelper($view);
	}

	public function tearDown() {
		parent::tearDown();

		Configure::write('App', $this->__resetAppConfig);
		Configure::write('Assetic', $this->__resetAsseticConfig);
		unset($this->Assetic, $this->__resetAppConfig, $this->__resetAsseticConfig);
	}

	public function asMock() {
		$view = $this->getMock('Cake\View\View', array('append'));
		$this->Assetic = $this->getMock('Gourmet\Assetic\View\Helper\AsseticHelper', [
			'_toPath',
		], array($view));
		$this->Assetic->Html = $this->getMock('Cake\View\Helper\HtmlHelper', array('css'), array($view));
	}

	public function testCssSingleFile() {
		$path = 'cake.generic';
		$full = $path . '.css';
		$options = [
			'debug' => true,
			'output' => null,
			'filters' => [],
			'block' => false
		];

		$this->asMock();

		$this->Assetic->expects($this->once())
			->method('_toPath')
			->with('css', $this->Assetic->resolveCssAsset($full), $options)
			->will($this->returnValue($full));

		$this->Assetic->Html->expects($this->once())
			->method('css')
			->with($full, array('block' => false))
			->will($this->returnValue('<link rel="stylesheet" href="/css/cake.generic.css"/>'));

		$result = $this->Assetic->css($path);
		$expected = '<link rel="stylesheet" href="/css/cake.generic.css"/>';
		$this->assertEquals($expected, $result);
	}

	public function testCssMultipleFiles() {
		$path = $full = ['one', 'two'];
		array_walk($full, function(&$file) {
			$file .= '.css';
		});
		$options = [
			'debug' => true,
			'output' => null,
			'filters' => [],
			'block' => false
		];

		$this->asMock();

		$expected = '';
		foreach ([0, 1] as $k) {
			$this->Assetic->expects($this->at($k))
				->method('_toPath')
				->with('css', $this->Assetic->resolveCssAsset($full[$k]), $options)
				->will($this->returnValue($full[$k]));

			$this->Assetic->Html->expects($this->at($k))
				->method('css')
				->with($full[$k], array('block' => false))
				->will($this->returnValue('<link rel="stylesheet" href="/css/' . $k . '.css"/>'));

			$expected .= '<link rel="stylesheet" href="/css/' . $k . '.css"/>';
		}

		$result = $this->Assetic->css($path);
		$this->assertEquals($expected, $result);
	}

	public function testCssGroupFile() {
		$group = ['main' => ['one', 'two']];
		$full = $group['main'];
		array_walk($full, function(&$file) {
			$file .= '.css';
		});
		$path = 'one';
		$options = [
			'debug' => true,
			'output' => null,
			'filters' => [],
			'block' => false
		];

		Configure::write('Assetic', ['cssGroups' => $group]);

		$this->asMock();

		$expected = '';
		foreach ([0, 1] as $k) {
			$this->Assetic->expects($this->at($k))
				->method('_toPath')
				->with('css', $this->Assetic->resolveCssAsset($full[$k]), $options)
				->will($this->returnValue($full[$k]));

			$this->Assetic->Html->expects($this->at($k))
				->method('css')
				->with($full[$k], array('block' => false))
				->will($this->returnValue('<link rel="stylesheet" href="/css/' . $k . '.css"/>'));

			$expected .= '<link rel="stylesheet" href="/css/' . $k . '.css"/>';
		}

		$result = $this->Assetic->css($path);
		$this->assertEquals($expected, $result);
	}

	public function testCssMix() {
		$group = ['main' => ['one', 'two']];
		$path = ['one', 'three'];
		$full = $group['main'];
		array_walk($full, function(&$file) {
			$file .= '.css';
		});
		$full[] = 'three.css';
		$options = [
			'debug' => true,
			'output' => null,
			'filters' => [],
			'block' => false
		];

		Configure::write('Assetic', ['cssGroups' => $group]);

		$this->asMock();

		$expected = '';
		foreach ([0, 1, 2] as $k) {
			$this->Assetic->expects($this->at($k))
				->method('_toPath')
				->with('css', $this->Assetic->resolveCssAsset($full[$k]), $options)
				->will($this->returnValue($full[$k]));

			$this->Assetic->Html->expects($this->at($k))
				->method('css')
				->with($full[$k], array('block' => false))
				->will($this->returnValue('<link rel="stylesheet" href="/css/' . $k . '.css"/>'));

			$expected .= '<link rel="stylesheet" href="/css/' . $k . '.css"/>';
		}

		$result = $this->Assetic->css($path);
		$this->assertEquals($expected, $result);
	}
}
