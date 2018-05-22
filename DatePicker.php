<?php

namespace maddoger\widgets;

use maddoger\behaviors\DateTimeAttribute;

/**
 * DateEditor Widget For Yii2 class file.
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 */

class DatePicker extends DateTimePicker
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!$this->phpFormat && $this->hasModel()) {
            $attribute = $this->model->{$this->attribute};
            if ($attribute instanceof DateTimeAttribute) {
                if (isset($attribute->localFormat[1])) {
                    $this->phpFormat = $attribute->localFormat[1];
                }
            }
        }
        if (!$this->phpFormat) {
            $this->phpFormat = \Yii::$app->formatter->dateFormat;
        }
        return parent::run();
    }
}
