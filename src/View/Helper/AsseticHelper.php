<?php

namespace Gourmet\Assetic\View\Helper;

use Assetic\Factory\Worker\CacheBustingWorker;
use Assetic\Filter\FilterInterface;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Hash;
use Cake\View\Helper;
use Cake\View\View;
use Gourmet\Assetic\AsseticTrait;

class AsseticHelper extends Helper {

	use AsseticTrait;

	public $helpers = ['Html'];

	protected $_blocks;

	protected $_defaultConfig = [
		'debug' => false,
		'cache' => true,
		'cdnUrl' => null,
		'cssFilters' => [],
		'cssOriginPath' => 'css',
		'cssTargetPath' => '_css',
		'imageFilters' => [],
		'imageOriginPath' => 'img',
		'imageTargetPath' => '_img',
		'jsFilters' => [],
		'jsOriginPath' => 'js',
		'jsTargetPath' => '_js',
	];

	protected $_groupMap;

	protected $_groups;

	protected $_initialized;

	public function __construct(View $View, array $config = array()) {
		$appConfig = Configure::read('App');

		$config += [
			'debug' => Configure::read('debug'),
		];

		$this->_defaultConfig = (array)Configure::read('Assetic') + [
			'debug' => $config['debug'],
			'cache' => !$config['debug'],
			'cssOriginPath' => $appConfig['cssBaseUrl'],
			'cssTargetPath' => '_' . $appConfig['cssBaseUrl'],
			'imageOriginPath' => $appConfig['imageBaseUrl'],
			'imageTargetPath' => '_' . $appConfig['imageBaseUrl'],
			'jsOriginPath' => $appConfig['jsBaseUrl'],
			'jsTargetPath' => '_' . $appConfig['jsBaseUrl'],
			'webrootOriginPath' => $appConfig['www_root'],
			'webrootTargetPath' => $appConfig['www_root'],
			'cssGroups' => [],
			'jsGroups' => [],
		] + $this->_defaultConfig;

		parent::__construct($View, $config);

		$this->_blocks = $this->_groupMap = $this->_groups = ['css' => [], 'js' => []];
		$this->_initialized = ['css' => false, 'image' => false, 'js' => false];

		$this->setWebrootPath($this->_config['webrootOriginPath'], 'origin');
		$this->setCssPath($this->_config['cssOriginPath'], 'origin');
		$this->setJsPath($this->_config['jsOriginPath'], 'origin');
		$this->setImagePath($this->_config['imageOriginPath'], 'origin');

		$this->setWebrootPath($this->_config['webrootTargetPath'], 'target');
		$this->setCssPath($this->_config['cssTargetPath'], 'target');
		$this->setJsPath($this->_config['jsTargetPath'], 'target');
		$this->setImagePath($this->_config['imageTargetPath'], 'target');

		Configure::write('Assetic', $this->_config);
	}

	public function css($path, array $options = array()) {
		return $this->_process('css', $path, $options);
	}

	public function fetch($block) {
		foreach (array_keys(array_filter($this->_initialized)) as $type) {
			if (!empty($this->_blocks[$type][$block])) {
				$asset = $this->_createAssetBlock($type, $this->_blocks[$type][$block]);
				$this->Html->{$type}(str_replace(rtrim(Configure::read('App.www_root'), DS), '', $asset->getTargetPath()), compact('block'));
			}
		}
		return $this->_View->fetch($block);
	}

	public function image($path, array $options = array()) {
		return $this->_process('image', $path, $options);
	}

/**
 * @throws Exception If invalid path or options.
 */
	public function normalizePathParam($type, $paths) {
		if ('image' == $type && (!is_string($paths) || strpos($paths, '*') !== false)) {
			throw new \Exception(sprintf('Invalid %s path', $type));
		}

		$paths = (array)$paths;
		foreach ($paths as $file => $options) {
			unset($paths[$file]);
			if (is_int($file)) {
				if (!is_string($options)) {
					throw new \Exception(sprintf('Invalid %s path', $type));
				}
				$file = $options;
				$options = [];
			} elseif (is_string($file)) {
				$options = (array)$options;
			} else {
				throw new \Exception(sprintf('Invalid %s path', $type));
			}

			if (is_string($options)) {
				$options = explode(',', $options);
			} elseif (!is_array($options)) {
				throw new \Exception(sprintf('Invalid %s options', $type));
			}

			if (empty($options)) {
				$options = array_keys($this->_config[$type . 'Filters']);
			}

			$paths[$this->_normalizeExtension($type, $file, $options)] = $options;
		}

		return $paths;
	}

	public function resolveCssGroup($file) {
		return $this->_resolveGroup('css', $file);
	}

	public function resolveJsGroup($file) {
		return $this->_resolveGroup('js', $file);
	}

	public function script($url, array $options = array()) {
		return $this->_process('js', $url, $options);
	}

	protected function _createAssetBlock($type, $files) {
		$factory = $this->_getFactory($type, $this->_config[$type . 'OriginPath']);
		$factory->setDebug($this->_config['debug']);
		$assets = [];

		foreach ($files as $file) {
			if (is_string($file)) {
				$name = substr($file, 1);
				$collection = $this->_getCollection(false);
				array_map(function($asset) use ($collection, $type) {
					$asset = $this->_resolveAsset($type, $this->_normalizeExtension($type, $asset));
					$collection->add($asset);
				}, $this->_config[$type . 'Groups'][$name]);
				$this->_getManager($type)->set($name, $collection);
			} else {
				$name = $factory->generateAssetName(array($file), array());
				$this->_getManager($type)->set($name, $file);
			}
			array_push($assets, '@' . $name);
		}

		$asset = $factory->createAsset($assets, $this->_config[$type . 'Filters'], array('output' => Configure::read('App.www_root') . $this->_config[$type . 'TargetPath'] . '*'));
		$this->_getWriter($type)->writeAsset($asset);
		return $asset;
	}

	protected function _initialize($type) {
		if ($this->_initialized[$type]) {
			return;
		}
		$this->_initialized[$type] = true;

		$this->_pushFilters($type, $this->_config[$type . 'Filters']);

		$manager = $this->_getManager($type, 'image' != $type && $this->_config['cache']);
		$factory = $this->_getFactory($type, $this->_config[$type . 'OriginPath']);
		$factory->setAssetManager($manager);
		$factory->setFilterManager($this->_getFilterManager($type));
		$factory->setDebug($this->_config['debug']);

		if ('image' == $type) {
			return;
		}

		if ($this->_config['cache']) {
			$factory->addWorker(new CacheBustingWorker($manager));
		}

		array_walk($this->_config[$type . 'Groups'], function(&$files, $output) use ($type, $factory) {
			$files = $this->normalizePathParam($type, $files);
			array_walk($files, function($filters, $file) use ($output, $type, $factory) {
				$name = $factory->generateAssetName($file, $filters, compact('output'));
				$asset = $this->_resolveAsset($type, $file, $this->_resolveFilters($type, $filters));
				$this->_getManager($type)->set($name, $asset);
				$this->_groupMap[$type][$file] = $output;
			});
		});
	}

	protected function _process($type, $files, array $options) {
		$altType = 'js' == $type ? 'script' : $type;
		$defaults = [
			'debug' => $this->_config['debug'],
			'output' => '',
			'filters' => []
		];

		$this->_initialize($type);

		$keepFilters = function($filter) {
			return !$this->_config['debug'] || '?' != $filter[0];
		};

		$options += $defaults + ['block' => false];
		$options['filters'] = $this->_resolveFilters($type, array_filter((array)$options['filters'], $keepFilters));
		$options['block'] = true === $options['block'] ? $altType : $options['block'];

		$htmlMethod = $altType;
		$htmlOptions = array_diff_key($options, $defaults);

		$output = [];

		$files = $this->normalizePathParam($type, $files);

		$callback = function($filters, $file) use (&$output, $type, $htmlMethod, $htmlOptions, $options) {
			$asset = $this->_resolveAsset($type, $file, $filters);
			array_push($output, $this->Html->{$htmlMethod}(
				$this->_toPath($type, $asset, $options),
				$htmlOptions
			));
		};

		foreach ((array)$files as $file => $filters) {
			$filters = array_unique($options['filters'] + $this->_resolveFilters($type, array_filter((array)$filters, $keepFilters)), SORT_REGULAR);

			if ('image' != $type && $group = $this->_resolveGroup($type, $file)) {
				if (isset($this->_groups[$type][$group])) {
					continue;
				}

				$this->_groups[$type][$group] = true;

				if ($options['block'] && !$options['debug']) {
					$this->_toBlock($type, $options['block'], '@' . $group);
					continue;
				}

				foreach ($this->_config[$type . 'Groups'][$group] as $file => $filters) {
					$asset = $this->_resolveAsset($type, $file, $this->_resolveFilters($type, $filters));
					array_push($output, $this->Html->{$htmlMethod}(
						$this->_toPath($type, $asset, $options),
						$htmlOptions
					));
				}

				continue;
			}

			$asset = $this->_resolveAsset($type, $file, $filters);

			if ($options['block'] && !$options['debug']) {
				$this->_toBlock($type, $options['block'], $asset);
				continue;
			}

			array_push($output, $this->Html->{$htmlMethod}(
				$this->_toPath($type, $asset, $options),
				$htmlOptions
			));
		}

		return implode('', $output);
	}

	protected function _resolveGroup($type, $file) {
		if (isset($this->_groupMap[$type][$file])) {
			return $this->_groupMap[$type][$file];
		}
		return false;
	}

	protected function _toBlock($type, $block, $file) {
		if (!isset($this->_blocks[$type][$block])) {
			$this->_blocks[$type][$block] = [];
		}
		$this->_blocks[$type][$block][] = $file;
	}

	protected function _toPath($type, $asset, $options) {
		$file = $asset->getSourcePath();
		$filters = $asset->getFilters();

		if (!empty($filters) || ($asset instanceof \Assetic\Asset\GlobAsset)) {
			$target = $options['output'] ? $options['output'] : $this->_normalizeFile($type, $file);
			$asset->setTargetPath($target);
			$this->_getWriter($type, $this->_config[$type . 'TargetPath'])->writeAsset($asset);
			$file = $this->normalizePath($this->getFilePath($target, ['type' => $type, 'src' => 'target', 'full' => false]), true);
		} else {
			$file = $this->normalizePath($this->getFilePath($file, ['full' => false]), true);
		}

		if ($this->_config['cdnUrl']) {
			$file = $this->_config['cdnUrl'] . $file;
		}

		return $file;
	}
}
