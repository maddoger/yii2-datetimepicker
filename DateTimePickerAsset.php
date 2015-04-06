<?php

namespace maddoger\widgets;

use yii\web\AssetBundle;

class DateTimePickerAsset extends AssetBundle
{
    public $sourcePath = '@bower/eonasdan-bootstrap-datetimepicker/build';

	public $css = [
		'css/bootstrap-datetimepicker.min.css',
	];

	public $js = [
		'js/bootstrap-datetimepicker.min.js',
	];

	public $depends = [
        'maddoger\widgets\MomentAsset',
        'yii\web\JqueryAsset',
	];
}