<?php

namespace maddoger\widgets;

/**
 * DateEditor Widget For Yii2 class file.
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 */

class DatePicker extends DateTimePicker
{
	public $jsFormat = 'DD.MM.YYYY';
	public $phpFormat = 'd.m.Y';

	public function init()
	{
		parent::init();
		$this->config['pickTime'] = false;
	}
}