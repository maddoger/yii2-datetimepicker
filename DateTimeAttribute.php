<?php

/**
 * @copyright Copyright (c) 2014 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\widgets;

use DateTime;
use DateTimeZone;
use Yii;
use yii\base\Object;
use yii\helpers\FormatConverter;

/**
 * Class DateTimeAttribute
 * @property string $value
 */
class DateTimeAttribute extends Object
{
    /**
     * @var DateTimeBehavior
     */
    public $behavior;
    /**
     * @var string
     */
    public $originalAttribute;
    /**
     * @var string
     */
    public $originalFormat;

    /**
     * @var string
     */
    public $originalTimeZone;

    /**
     * @var string
     */
    public $localAttribute;

    /**
     * @var string|array
     */
    public $localFormat;

    /**
     * @var string
     */
    public $localTimeZone;

    /**
     * @var bool true for datetime and time
     */
    public $timeZoneConvert;

    /**
     * @var string
     */
    protected $_localFormatPhp;

    /**
     * @var string
     */
    protected $_value;

    public function init()
    {
        parent::init();
        if ($this->timeZoneConvert === null) {
            $this->timeZoneConvert = !($this->originalFormat != 'U' && $this->localFormat[0] == 'date');
        }
        if (!$this->_localFormatPhp) {
            $this->_localFormatPhp = FormatConverter::convertDateIcuToPhp($this->localFormat[1], $this->localFormat[0]);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

    /**
     * @return string
     */
    public function getValue()
    {
        if ($this->_value) {
            return $this->_value;
        }

        $datetime = static::parseDateValue(
            $this->behavior->owner->{$this->originalAttribute},
            $this->originalFormat,
            $this->originalTimeZone
        );
        if ($datetime === false) {
            return null;
        } else {
            if ($this->timeZoneConvert) {
                $datetime->setTimezone(new DateTimeZone($this->localTimeZone));
            }
            return $this->behavior->formatter->format($datetime, $this->localFormat);
        }
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->_value = $value;

        $datetime = static::parseDateValue(
            $value,
            $this->_localFormatPhp,
            $this->localTimeZone
        );

        if ($datetime === false) {
            $this->behavior->owner->{$this->originalAttribute} = null;
        } else {
            if ($this->timeZoneConvert) {
                $datetime->setTimezone(new DateTimeZone($this->originalTimeZone));
            }
            $this->behavior->owner->{$this->originalAttribute} =
                $datetime->format($this->originalFormat);
        }
    }

    /**
     * Parses date string into DateTime object
     *
     * @param string $value string representing date
     * @param string $format string representing date
     * @param string $timeZone string representing date
     * @return boolean|DateTime DateTime object or false on failure
     */
    protected static function parseDateValue($value, $format, $timeZone)
    {
        $date = DateTime::createFromFormat($format, $value, new DateTimeZone($timeZone));
        $errors = DateTime::getLastErrors();
        if ($date === false || $errors['error_count'] || $errors['warning_count']) {
            return false;
        } else {
            // if no time was provided in the format string set time to 0 to get a simple date timestamp
            if (strpbrk($format, 'UHhGgis') === false) {
                $date->setTime(0, 0, 0);
            }
            return $date;
        }
    }
}