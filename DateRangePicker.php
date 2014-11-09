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
        $this->clientOptions['pickTime'] = false;
        $this->clientOptions2['pickTime'] = false;

        $this->phpFormat = \Yii::$app->formatter->dateFormat;
		parent::init();
	}
}