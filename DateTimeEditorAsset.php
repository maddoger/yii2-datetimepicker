<?php

namespace maddoger\widgets;

use yii\web\AssetBundle;

class DateTimeEditorAsset extends AssetBundle
{
	public $css = [
		'bootstrap-datetimepicker.min.css',
	];

	public $js = [
        'moment-with-locales.js',
		'bootstrap-datetimepicker.min.js',
	];

	public $depends = [
		'yii\bootstrap\BootstrapAsset',
		'yii\bootstrap\BootstrapPluginAsset',
	];

    public function init()
    {
        parent::init();
        $this->sourcePath = __DIR__.'/assets';
    }
}