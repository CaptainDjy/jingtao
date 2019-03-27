# clockpicker asset for Yii2
Yii2 wrapper for [clockpicker]( https://github.com/zhang988925/yii2-clockpicker.git ) (clock-like time picker) plugin.


## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
composer require --prefer-dist "dh/yii2-clockpicker:*"
```

or add

```
"dh/yii2-clockpicker": "0.0.1"
```

to the `require` section of your `composer.json` file.


## Usage

In the view file register the asset:

```php
\dh\clockpicker\ClockPickerAsset::register($this);
```


## License

The BSD License (BSD). Please see [License File](LICENSE.md) for more information.
