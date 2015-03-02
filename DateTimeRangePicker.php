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

class DateTimeRangePicker extends InputWidget
{
    /**
     * @var array plugin options
     */
    public $clientOptions = [];

    /**
     * @var array text field options
     */
    public $options = [];

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
     * @var array
     */
    public $clientOptions2;

    /**
     * @var array
     */
    public $options2;
    
    /**
     * @var string name of textarea tag or name of attribute
     */
    public $attribute2;

    /**
     * @var string value for text area (without model)
     */
    public $value2;

    /**
     * @var string
     */
    public $delimiter = '<span class="input-group-addon">-</span>';


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
        if (!isset($this->options2['class'])) {
            $this->options2['class'] = 'form-control';
        }


        if (!$this->phpFormat) {
            $this->phpFormat = Yii::$app->formatter->datetimeFormat;
        }
        if (!$this->jsFormat) {
            $this->jsFormat = DateTimePicker::convertPhpDateToMomentJs(FormatConverter::convertDateIcuToPhp($this->phpFormat));
        }

    }

    /**
     * Renders the widget.
     */
    public function run()
    {
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

        $fieldName2 = $this->name ? $this->name : $this->attribute2;
        if (!is_null($this->model)) {
            $fieldName2 = Html::getInputName($this->model, $this->attribute2);
            if (!array_key_exists('id', $this->options2)) {
                $this->options2['id'] = Html::getInputId($this->model, $this->attribute2);
            }
            if (!$this->value2) {
                $this->value2 = Html::getAttributeValue($this->model, $this->attribute2);
                if ($this->reformatValue && $this->value2 !== null) {
                    try
                    {
                        $this->value2 = Yii::$app->formatter->asDatetime($this->value2, $this->phpFormat);
                    } catch (\Exception $e){}
                }
            }
        }

        $this->registerClientScript();

        $res = '<div id="'.$this->getId().'" class="input-group">';
        $res .= Html::textInput($fieldName, $this->value, $this->options);
        $res .= $this->delimiter;
        $res .= Html::textInput($fieldName2, $this->value2, $this->options2);
        $res .= '</div>';

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
         * locale fix
         * @author <https://github.com/sim2github>
         */
        if (!isset($this->clientOptions['locale'])) {
            $applocale = strtolower(substr(Yii::$app->language , 0, 2)); //First 2 letters
            $this->clientOptions['locale'] = $applocale;
        }
        if (!isset($this->clientOptions['format'])) {
            $this->clientOptions['format'] = $this->jsFormat;
        }
        if (!isset($this->clientOptions2['locale'])) {
            $applocale = strtolower(substr(Yii::$app->language , 0, 2)); //First 2 letters
            $this->clientOptions2['locale'] = $applocale;
        }
        if (!isset($this->clientOptions2['format'])) {
            $this->clientOptions2['format'] = $this->jsFormat;
        }

        $config = empty($this->clientOptions) ? '' : Json::encode($this->clientOptions);
        $config2 = empty($this->clientOptions2) ? '' : Json::encode($this->clientOptions2);

        $js = "$('#" . $this->options['id'] . "').datetimepicker($config);";
        $js .= "$('#" . $this->options2['id'] . "').datetimepicker($config2);";
        $view->registerJs($js);
    }
}