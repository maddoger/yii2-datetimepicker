<?php

namespace maddoger\widgets;

use Yii;
use yii\helpers\FormatConverter;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * DateTimeEditor Widget For Yii2 class file.
 *
 * @property array $plugins
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 */

class DateTimePicker extends InputWidget
{
    /**
     * @var array text field options
     */
    public $options = [];

    /**
     * @var array plugin options
     */
    public $clientOptions = [];

    /**
     * @var array plugin events
     * ```
     * [
     *  'dp.change' => new JsExpression('function(event){ alert('event'); }')
     * ]
     * ```
     */
    public $clientEvents = [];

    /**
     * @var array
     */
    public $containerOptions = [];

    /**
     * @var string format
     */
    public $jsFormat;

    /**
     * @var string format
     */
    public $phpFormat;

    /**
     * @var bool Reformat value from model to phpFormat
     */
    public $reformatValue = false;

    /**
     * @var string addon template
     */
    public $addon = '<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>';

    /**
     * @var bool addon template before or after
     */
    public $addonBefore = true;

    /**
     * @var string selector for init js scripts
     */
    protected $selector = null;

    /**
     * Initializes the widget.
     * If you override this method, make sure you call the parent implementation first.
     */
    public function init()
    {
        parent::init();

        if (!isset($this->options['class'])) {
            $this->options['class'] = 'form-control';
        }
        if (!isset($this->containerOptions['class'])) {
            $this->containerOptions['class'] = 'input-group datetime-editor';
        }
    }

    /**
     * Renders the widget.
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
            $this->phpFormat = Yii::$app->formatter->datetimeFormat;
        }

        $res = '';
        if (!$this->selector) {
            $this->selector = '#' . $this->getId();

            $fieldName = $this->name ? $this->name : $this->attribute;

            if (!is_null($this->model)) {
                $fieldName = Html::getInputName($this->model, $this->attribute);
                if (!array_key_exists('id', $this->options)) {
                    $this->options['id'] = Html::getInputId($this->model, $this->attribute);
                }
                if (!$this->value) {
                    $this->value = Html::getAttributeValue($this->model, $this->attribute);
                    if ($this->reformatValue && $this->value !== null) {
                        try
                        {
                            $this->value = Yii::$app->formatter->asDatetime($this->value, $this->phpFormat);
                        } catch (\Exception $e){}
                    }
                }
            }

            $this->containerOptions['id'] = $this->getId();

            if ($this->addonBefore === true) {
                $res .= $this->addon;
            }
            $res .= Html::textInput($fieldName, $this->value, $this->options);
            if ($this->addonBefore === false) {
                $res .= $this->addon;
            }
            $res = Html::tag('div', $res, $this->containerOptions);
        }

        $this->registerClientScript();
        return $res;
    }

    /**
     * Registers CKEditor JS
     */
    protected function registerClientScript()
    {
        $view = $this->getView();
        DateTimePickerAsset::register($view);

        /*
         * Language fix
         * @author <https://github.com/sim2github>
         */
        if (!isset($this->clientOptions['locale'])) {
            $appLanguage = strtolower(substr(Yii::$app->language , 0, 2)); //First 2 letters
            $this->clientOptions['locale'] = $appLanguage;
        }

        if (!$this->jsFormat) {
            $this->jsFormat = static::convertPhpDateToMomentJs(FormatConverter::convertDateIcuToPhp($this->phpFormat));
        }
        if (!isset($this->clientOptions['format'])) {
            $this->clientOptions['format'] = $this->jsFormat;
        }

        if (!isset($this->clientOptions['minDate'])) {
            $this->clientOptions['minDate'] = '1900-01-01';
        }
        if (!isset($this->clientOptions['widgetPositioning'])) {
            $this->clientOptions['widgetPositioning'] = [
                'horizontal' => $this->addonBefore ? 'left' : 'right',
                'vertical' => 'auto',
            ];
        }

        $config = empty($this->clientOptions) ? '' : Json::encode($this->clientOptions);

        $js[] = "$('" . $this->selector . "').datetimepicker($config)";
        foreach ($this->clientEvents as $key=>$value) {
            $js[] =  ".on('{$key}', {$value})";
        }
        $js[] = ";\n";

        $view->registerJs(implode('', $js));
    }

    /**
     * @param $pattern
     * @return string
     */
    public static function convertPhpDateToMomentJs($pattern)
    {
        return strtr($pattern, [
            // Day
            'd' => 'DD',    // Day of the month, 2 digits with leading zeros 	01 to 31
            'D' => 'ddd',   // A textual representation of a day, three letters 	Mon through Sun
            'j' => 'D',     // Day of the month without leading zeros 	1 to 31
            'l' => 'dddd',  // A full textual representation of the day of the week 	Sunday through Saturday
            'N' => 'E',     // ISO-8601 numeric representation of the day of the week, 1 (for Monday) through 7 (for Sunday)
            'S' => '',      // English ordinal suffix for the day of the month, 2 characters 	st, nd, rd or th. Works well with j
            'w' => 'd',      // Numeric representation of the day of the week 	0 (for Sunday) through 6 (for Saturday)
            'z' => '',     // The day of the year (starting from 0) 	0 through 365
            // Week
            'W' => 'W',     // ISO-8601 week number of year, weeks starting on Monday (added in PHP 4.1.0) 	Example: 42 (the 42nd week in the year)
            // Month
            'F' => 'MMMM',  // A full textual representation of a month, January through December
            'm' => 'MM',    // Numeric representation of a month, with leading zeros 	01 through 12
            'M' => 'MMM',   // A short textual representation of a month, three letters 	Jan through Dec
            'n' => 'M',     // Numeric representation of a month, without leading zeros 	1 through 12, not supported by ICU but we fallback to "with leading zero"
            't' => '',      // Number of days in the given month 	28 through 31
            // Year
            'L' => '',      // Whether it's a leap year, 1 if it is a leap year, 0 otherwise.
            'o' => 'GGGG',     // ISO-8601 year number. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead.
            'Y' => 'YYYY',  // A full numeric representation of a year, 4 digits 	Examples: 1999 or 2003
            'y' => 'YY',    // A two digit representation of a year 	Examples: 99 or 03
            // Time
            'a' => 'a',     // Lowercase Ante meridiem and Post meridiem, am or pm
            'A' => 'A',     // Uppercase Ante meridiem and Post meridiem, AM or PM, not supported by ICU but we fallback to lowercase
            'B' => '',      // Swatch Internet time 	000 through 999
            'g' => 'h',     // 12-hour format of an hour without leading zeros 	1 through 12
            'G' => 'H',     // 24-hour format of an hour without leading zeros 0 to 23h
            'h' => 'hh',    // 12-hour format of an hour with leading zeros, 01 to 12 h
            'H' => 'HH',    // 24-hour format of an hour with leading zeros, 00 to 23 h
            'i' => 'mm',    // Minutes with leading zeros 	00 to 59
            's' => 'ss',    // Seconds, with leading zeros 	00 through 59
            'u' => '[u]',      // Microseconds. Example: 654321
            // Timezone
            'e' => '[e]',    // Timezone identifier. Examples: UTC, GMT, Atlantic/Azores
            'I' => '',      // Whether or not the date is in daylight saving time, 1 if Daylight Saving Time, 0 otherwise.
            'O' => 'ZZ',    // Difference to Greenwich time (GMT) in hours, Example: +0200
            'P' => 'Z',   // Difference to Greenwich time (GMT) with colon between hours and minutes, Example: +02:00
            'T' => '[T]',   // Timezone abbreviation, Examples: EST, MDT ...
            'Z' => '',    // Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive. -43200 through 50400
            // Full Date/Time
            'c' => 'YYYY-MM-DD[T]HH:mm:ssZ', // ISO 8601 date, e.g. 2004-02-12T15:19:21+00:00
            'r' => 'ddd, DD MMM YYYY HH:mm:ss ZZ', // RFC 2822 formatted date, Example: Thu, 21 Dec 2000 16:01:07 +0200
            'U' => 'X',      // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
        ]);
    }
}