<?php

namespace maddoger\widgets;

/**
 * DateEditor Widget For Yii2 class file.
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 */

class DateRangePicker extends DateTimeRangePicker
{
	public function init()
	{
        $this->phpFormat = \Yii::$app->formatter->dateFormat;
		parent::init();
	}
}