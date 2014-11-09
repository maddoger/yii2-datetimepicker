<?php

namespace maddoger\widgets;

use yii\web\AssetBundle;

class DateTimePickerAsset extends AssetBundle
{
	public $css = [
		'@bower/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
	];

	public $js = [
        '@bower/moment/min/moment-with-locales-min.js',
		'@bower/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
	];

	public $depends = [
		'yii\bootstrap\BootstrapAsset',
		'yii\bootstrap\BootstrapPluginAsset',
	];
}