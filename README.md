# Assetic for CakePHP

Built to seamlessly integrate [Assetic][assetic] with [CakePHP][cakephp].

__This is an unstable repository and should be treated as an alpha.__

## Install

```
composer require gourmet/assetic:*
```

or by adding this package to your project's `composer.json`:

```
"require": {
	"gourmet/assetic": "*"
}
```

## Usage

The `AsseticHelper` methods work somewhat like their `HtmlHelper` counter-parts
but with some added options.

The methods are:

* __css__(_$path_, _$options_)
* __image__(_$path_, _$options_)
* __script__(_$url, _$options_)

The added `$options` keys are:

* __debug__: _boolean_ Defaults to app's configuration `debug` value.
* __output__: _string_ Name of the Assetic created asset.
* __filters__: _array|string_ Filter(s) to use. Defaults to the configured
filters for the asset type (css, image, js).

The `$path` passed to `css()` and the `$url` passed to `script` can be passed
as a string or as an associative array (see examples below).

## Examples

```php
echo $this->Assetic->css('cake.generic', ['debug' => false, 'filters' => 'cssmin']);
echo $this->Assetic->css(['cake.generic' => 'cssmin'], ['debug' => false]);
echo $this->Assetic->css('cake.generic');
```

All the above examples will have the same result, including the minified version
of `cake.generic.css`. For the last example, it is assumed that the value of
the `Assetic.cssFilters` configuration contains at least the `cssmin` key.


```php
$this->Assetic->css('cake.generic', ['filters' => 'cssmin,?uglify']);
```

In the above example, when in debug mode, only the `cssmin` filter will be run.

[assetic]:https://github.com/kriswallsmith/assetic
[cakephp]:http://cakephp.org
