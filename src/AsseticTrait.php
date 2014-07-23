<?php

namespace Gourmet\Assetic;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\AssetManager;
use Assetic\AssetWriter;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\LazyAssetManager;
use Assetic\FilterManager;
use Cake\Core\Configure;
use Cake\Utility\Hash;

use Psr\Log\InvalidArgumentException;

trait AsseticTrait {

	public $cssCollection;
	public $cssFactory;
	public $cssFilterManager;
	public $cssLazyManager;
	public $cssManager;
	public $cssWriter;

	public $imageCollection;
	public $imageFactory;
	public $imageFilterManager;
	public $imageLazyManager;
	public $imageManager;
	public $imageWriter;

	public $jsCollection;
	public $jsFactory;
	public $jsFilterManager;
	public $jsLazyManager;
	public $jsManager;
	public $jsWriter;

	private $__paths = ['css' => [], 'image' => [], 'js' => [], 'webroot' => []];
	private $__types = ['css', 'image', 'js'];

	public function getCssCollection($byReference = true) {
		return $this->_getCollection('css', $byReference);
	}

	public function getCssFactory($targetPath, $byReference = true) {
		return $this->_getFactory('css', $targetPath, $byReference);
	}

	public function getCssFilterManager($byReference = true) {
		return $this->_getFilterManager('css', $byReference);
	}

	public function getCssManager($byReference = true, $lazy = false) {
		return $this->_getManager('css', $byReference, $lazy);
	}

	public function getCssPath($src = 'origin') {
		return $this->__paths['css'][$src];
	}

	public function getCssWriter($targetPath = null, $byReference = true) {
		return $this->_getWriter('css', $targetPath, $byReference);
	}

	public function getFilePath($file, array $options = array()) {
		$options += ['type' => $this->getFileType($file), 'src' => 'origin', 'full' => true];

		if (!in_array($options['type'], $this->__types)) {
			throw new InvalidArgumentException(sprintf('The passed type `%s` is not a valid type.', $type));
		}

		$key = $options['type'] . ucfirst($options['src']) . 'Path';

		$path = $this->__paths[$options['type']][$options['src']];
		if (!$options['full']) {
			$path = str_replace($this->__paths['webroot'][$options['src']], '', $path);
		}

		return $this->normalizePath($path) . $file;
	}

	public function getFileType($file) {
		$ext = pathinfo($file, PATHINFO_EXTENSION);

		$type = 'image';
		if (in_array($ext, ['css', 'less', 'sass', 'scss'])) {
			$type = 'css';
		} elseif (in_array($ext, ['js', 'coffee'])) {
			$type = 'js';
		}

		return $type;
	}

	public function getImageCollection($byReference = true) {
		return $this->_getCollection('image', $byReference);
	}

	public function getImageFactory($targetPath, $byReference = true) {
		return $this->_getFactory('image', $targetPath, $byReference);
	}

	public function getImageFilterManager($byReference = true) {
		return $this->_getFilterManager('image', $byReference);
	}

	public function getImageManager($byReference = true, $lazy = false) {
		return $this->_getManager('image', $byReference, $lazy);
	}

	public function getImagePath($src = 'origin') {
		return $this->__paths['image'][$src];
	}

	public function getImageWriter($targetPath = null, $byReference = true) {
		return $this->_getWriter('image', $targetPath, $byReference);
	}

	public function getJsCollection($byReference = true) {
		return $this->_getCollection('js', $byReference);
	}

	public function getJsFactory($targetPath, $byReference = true) {
		return $this->_getFactory('js', $targetPath, $byReference);
	}

	public function getJsFilterManager($byReference = true) {
		return $this->_getFilterManager('js', $byReference);
	}

	public function getJsManager($byReference = true, $lazy = false) {
		return $this->_getManager('js', $byReference, $lazy);
	}

	public function getJsPath($src = 'origin') {
		return $this->__paths['js'][$src];
	}

	public function getWebrootPath($src = 'origin') {
		return $this->__paths['webroot'][$src];
	}

	public function getJsWriter($targetPath = null, $byReference = true) {
		return $this->_getWriter('js', $targetPath, $byReference);
	}

	public function normalizeCssExtension($file, $filters = array()) {
		return $this->_normalizeExtension('css', $file, $filters);
	}

	public function normalizeCssFile($file) {
		return $this->_normalizeFile('css', $file);
	}

	public function normalizeImageExtension($file, $filters = array()) {
		return $this->_normalizeExtension('image', $file, $filters);
	}

	public function normalizeImageFile($file) {
		return $this->_normalizeFile('image', $file);
	}

	public function normalizeFilter($filter) {
		if (is_array($filter)) {
			$filters = $filter;
			array_walk($filters, function(&$filter) {
				$filter = $this->normalizeFilter($filter);
			});
			return $filters;
		}

		if (is_a($filter, '\Assetic\Filter\FilterInterface')) {
			return $filter;
		}

		$filter = (array)$filter;
		switch (count($filter)) {
			case 3:
				$filter = new $filter[0]($filter[1], $filter[2]);
				break;
			case 2:
				$filter = new $filter[0]($filter[1]);
				break;
			default:
				$filter = new $filter[0]();
		}

		return $filter;
	}

	public function normalizeJsExtension($file, $filters = array()) {
		return $this->_normalizeExtension('js', $file, $filters);
	}

	public function normalizeJsFile($file) {
		return $this->_normalizeFile('js', $file);
	}

	public function normalizePath($path, $options = array()) {
		if (true === $options) {
			$options = ['append' => false, 'prepend' => true];
		} else {
			if (false === $options) {
				$options = [];
			}
			$options += ['append' => true, 'prepend' => false];
		}

		if ($options['prepend'] && substr($path, 0) != DS) {
			$path = DS . $path;
		}

		if ($options['append'] && substr($path, -1) != DS) {
			$path .= DS;
		}

		return $path;
	}

	public function pushCssFilters($filters) {
		$this->_pushFilters('css', $filters);
	}

	public function pushImageFilters($filters) {
		$this->_pushFilters('image', $filters);
	}

	public function pushJsFilters($filters) {
		$this->_pushFilters('js', $filters);
	}

	public function resolveCssAsset($file, $filters = array()) {
		return $this->_resolveAsset('css', $file, $filters);
	}

	public function resolveCssCollection($files) {
		return $this->_resolveCollection('css', $files);
	}

	public function resolveImageFilters($filters) {
		return $this->_resolveFilters('image', $filters);
	}

	public function resolveImageAsset($file, $filters = array()) {
		return $this->_resolveAsset('image', $file, $filters);
	}

	public function resolveImageCollection($files) {
		return $this->_resolveCollection('image', $files);
	}

	public function resolveCssFilters($filters) {
		return $this->_resolveFilters('css', $filters);
	}

	public function resolveJsAsset($file, $filters = array()) {
		return $this->_resolveAsset('js', $file, $filters);
	}

	public function resolveJsCollection($files) {
		return $this->_resolveCollection('js', $files);
	}

	public function resolveJsFilters($filters) {
		return $this->_resolveFilters('js', $filters);
	}

	public function setCssPath($path, $src = 'origin') {
		$this->_setPath('css', $src, $path);
	}

	public function setImagePath($path, $src = 'origin') {
		$this->_setPath('image', $src, $path);
	}

	public function setJsPath($path, $src = 'origin') {
		$this->_setPath('js', $src, $path);
	}

	public function setWebrootPath($path, $src = 'origin') {
		$this->_setPath('webroot', $src, $path);
	}

	protected function _getCollection($type, $byReference = true) {
		$varname = $type . 'Collection';
		$ret = $this->{$varname};

		if (!$byReference || empty($ret)) {
			$ret = new AssetCollection();
			if ($byReference) {
				$this->{$varname} = $ret;
			}
		}

		return $ret;
	}

	protected function _getFactory($type, $targetPath, $byReference = true) {
		$varname = $type . 'Factory';
		$ret = $this->{$varname};

		if (!$byReference || empty($ret)) {
			$ret = new AssetFactory($targetPath);
			if ($byReference) {
				$this->{$varname} = $ret;
				$this->{$varname}->setAssetManager($this->_getManager($type));
				$this->{$varname}->setFilterManager($this->_getFilterManager($type));
			}
		}

		return $ret;
	}

	protected function _getFilterManager($type, $byReference = true) {
		$varname = $type . 'FilterManager';
		$ret = $this->{$varname};

		if (!$byReference || empty($ret)) {
			$ret = new FilterManager();
			if ($byReference) {
				$this->{$varname} = $ret;
			}
		}

		return $ret;
	}

	protected function _getManager($type, $byReference = true, $lazy = false) {
		$varname = $type . (empty($lazy) ? '' : 'Lazy') . 'Manager';
		$ret = $this->{$varname};

		if (!$byReference || empty($ret)) {
			$ret = empty($lazy) ? new AssetManager() : new LazyAssetManager($lazy);
			if ($byReference) {
				$this->{$varname} = $ret;
			}
		}

		return $ret;
	}

	protected function _getWriter($type, $targetPath = null, $byReference = true) {
		$varname = $type . 'Writer';
		$ret = $this->{$varname};

		if (empty($targetPath)) {
			$byReference = true;
		}

		if (!$byReference || empty($ret)) {
			$ret = new AssetWriter($targetPath);
			if ($byReference) {
				$this->{$varname} = $ret;
			}
		}

		return $ret;
	}

	protected function _normalizeExtension($type, $file, $filters = array()) {
		if (is_array($file)) {
			$files = $file;
			array_walk($files, function(&$file) use ($type, $filters) {
				$file = $this->_normalizeExtension($type, $file, $filters);
			});
			return $files;
		}

		if (!Hash::numeric(array_keys($filters))) {
			$filters = array_keys($filters);
		}

		if ('@' == $file[0]) {
			// do nothing
		} elseif ('js' == $type) {
			if (preg_match('/\.(coffee|js)$/is', $file)) {
				// do nothing
			} elseif (
				in_array('coffee', $filters)
				&& file_exists($this->__paths[$type]['origin'] . $file . '.coffee')
			) {
				$file .= '.coffee';
			} else {
				$file .= '.js';
			}
		} elseif ('css' == $type) {
			if (preg_match('/\.(css|less|sass|scss)$/is', $file)) {
				// do nothing
			} elseif (
				array_intersect(array('less', 'lessphp'), $filters)
				&& file_exists($this->__paths[$type]['origin'] . $file . '.less')
			) {
				$file .= '.less';
			} elseif (
				array_intersect(array('sass', 'scss', 'scssphp'), $filters)
				&& file_exists($this->__paths[$type]['origin'] . $file . '.scss')
			) {
				$file .= '.scss';
			} else {
				$file .= '.css';
			}
		}

		return $file;
	}

	protected function _normalizeFile($type, $file) {
		$pattern = '/([^\.]+)(.css|.less|.sass|.scss)?$/is';
		$replacement = '$1.css';
		if ('js' == $type) {
			$pattern = '/([^\.]+)(.js)?$/is';
			$replacement = '$1.js';
		}

		return str_replace('*', 'combined', preg_replace($pattern, $replacement, $file));
	}

	protected function _pushFilters($type, $filters) {
		$filterManager = $this->_getFilterManager($type);

		$callback = function($key, $filter) use ($filterManager) {
			$filter = $this->normalizeFilter($filter);
			$filterManager->set($key, $filter);
		};

		array_map($callback, array_keys($filters), $filters);
	}

	protected function _resolveAsset($type, $file, $filters = array()) {
		if (!in_array($type, $this->__types)) {
			throw new InvalidArgumentException(sprintf('The passed type `%s` is not a valid type.', $type));
		}

		$path = $this->getFilePath($file, compact('type'));

		if (strpos($path, '*') !== false) {
			return new GlobAsset($path, $filters);
		}

		return new FileAsset($path, $filters, null, $file);
	}

	protected function _resolveCollection($type, $files) {
		if (!in_array($type, $this->__types)) {
			throw new InvalidArgumentException(sprintf('The passed type `%s` is not a valid type.', $type));
		}

		$collection = $this->_getCollection($type, false);
		array_map(function ($file) use ($collection, $type) {
			$collection->add($this->_resolveAsset($type, $file));
		}, (array)$files);
		return $collection;
	}

	protected function _resolveFilters($type, $filters) {
		$filters = (array)$filters;
		$filterManager = $this->_getFilterManager($type);

		$keys = $filters;
		if (!Hash::numeric(array_keys($keys))) {
			$keys = array_keys($keys);
		}

		array_walk($filters, function(&$filter) use ($filterManager) {
			if ('?' == $filter[0]) {
				$filter = substr($filter, 1);
			}

			if (!$filterManager->has($filter)) {
				throw new InvalidArgumentException(sprintf('Filter `%s` not configured.', $filter));
			}

			$filter = $filterManager->get($filter);
		});

		return array_combine($keys, $filters);
	}

	protected function _setPath($type, $src, $path) {
		if (!empty($path)) {
			if (!empty($this->__paths['webroot'][$src]) && strpos($path, $this->__paths['webroot'][$src]) === false) {
				$path = $this->__paths['webroot'][$src] . $path;
			}
			$path = $this->normalizePath($path);
		}
		$this->__paths[$type][$src] = $path;
	}

}
