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
    public $pluginOptions = [];

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
    public $pluginOptions2;

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
        if (!isset($this->pluginOptions['pickDate'])) {
            $this->pluginOptions['pickDate'] = true;
        }
        if (!isset($this->pluginOptions['pickTime'])) {
            $this->pluginOptions['pickTime'] = true;
        }

        if (!isset($this->pluginOptions2['pickDate'])) {
            $this->pluginOptions2['pickDate'] = true;
        }
        if (!isset($this->pluginOptions2['pickTime'])) {
            $this->pluginOptions2['pickTime'] = true;
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
        if (!isset($this->options['class'])) {
            $this->options['class'] = 'form-control';
        }
        if (!isset($this->options2['class'])) {
            $this->options2['class'] = 'form-control';
        }

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


        echo '<div id="'.$this->getId().'" class="input-group">';
        echo Html::textInput($fieldName, $this->value, $this->options);
        echo $this->delimiter;
        echo Html::textInput($fieldName2, $this->value2, $this->options2);
        echo '</div>';

        DateTimePickerAsset::register($this->getView());
        $this->registerClientScript();
    }

    /**
     * Registers CKEditor JS
     */
    protected function registerClientScript()
    {
        $view = $this->getView();

        /*
         * Language fix
         * @author <https://github.com/sim2github>
         */
        if (!isset($this->pluginOptions['language'])) {
            $appLanguage = strtolower(substr(Yii::$app->language , 0, 2)); //First 2 letters
            $this->pluginOptions['language'] = $appLanguage;
        }
        if (!isset($this->pluginOptions['format'])) {
            $this->pluginOptions['format'] = $this->jsFormat;
        }
        if (!isset($this->pluginOptions2['language'])) {
            $appLanguage = strtolower(substr(Yii::$app->language , 0, 2)); //First 2 letters
            $this->pluginOptions2['language'] = $appLanguage;
        }
        if (!isset($this->pluginOptions2['format'])) {
            $this->pluginOptions2['format'] = $this->jsFormat;
        }

        $config = empty($this->pluginOptions) ? '' : Json::encode($this->pluginOptions);
        $config2 = empty($this->pluginOptions2) ? '' : Json::encode($this->pluginOptions2);

        $js = "$('#" . $this->options['id'] . "').datetimepicker($config);";
        $js .= "$('#" . $this->options2['id'] . "').datetimepicker($config2);";
        $view->registerJs($js);
    }
}