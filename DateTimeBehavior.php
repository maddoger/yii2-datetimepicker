<?php

/**
 * @copyright Copyright (c) 2014 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\widgets;

use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\base\InvalidParamException;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\i18n\Formatter;
use yii\validators\DateValidator;

/**
 * Class DateTimeBehavior
 *
 */
class DateTimeBehavior extends Behavior
{
    /**
     * @var array of attributes names
     */
    public $attributes = [];

    /**
     * @var Formatter
     */
    public $formatter;

    /**
     * @var string
     * If this property is not set, [[\yii\base\Application::timeZone]] will be used.
     */
    public $timeZone;

    /**
     * @var string|array
     */
    public $format = 'datetime';

    /**
     * @var string
     */
    public $originalFormat = 'U';

    /**
     * @var string
     */
    public $originalTimeZone = 'UTC';

    /**
     * @var array
     */
    public $attributeConfig = ['class' => '\maddoger\widgets\DateTimeAttribute'];

    /**
     * @var string
     */
    public $namingTemplate = '{attribute}_local';

    /**
     * @var bool
     */
    public $performValidation = true;

    /**
     * @var array
     */
    public $validatorOptions = [];

    /**
     * @var DateTimeAttribute[]
     */
    public $attributeValues = [];

    /**
     * @param string|array $format
     * @throws InvalidParamException
     * @return array|string
     */
    public function normalizeFormat($format)
    {
        if (is_string($format)) {
            switch ($format) {
                case 'date':
                    return ['date', $this->formatter->dateFormat];
                case 'time':
                    return ['time', $this->formatter->timeFormat];
                case 'datetime':
                    return ['datetime', $this->formatter->datetimeFormat];
                default:
                    throw new InvalidParamException('$format has incorrect value');
            }
        }
        return $format;
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (is_null($this->formatter)) {
            $this->formatter = Yii::$app->formatter;
        } elseif (is_array($this->formatter)) {
            $this->formatter = Yii::createObject($this->formatter);
        }

        if (!$this->timeZone) {
            $this->timeZone = Yii::$app->timeZone;
        }

        $this->prepareAttributes();
    }

    protected function prepareAttributes()
    {
        foreach ($this->attributes as $key => $value) {
            $config = $this->attributeConfig;
            $config['localTimeZone'] = $this->timeZone;
            $config['localFormat'] = $this->normalizeFormat($this->format);
            $config['originalFormat'] = $this->originalFormat;
            $config['originalTimeZone'] = $this->originalTimeZone;

            if (is_integer($key)) {
                $originalAttribute = $value;
                $localAttribute = $this->processTemplate($originalAttribute);
            } else {
                $originalAttribute = $key;
                if (is_string($value)) {
                    $localAttribute = $value;
                } else {
                    $localAttribute = ArrayHelper::remove($value, 'localAttribute',
                        $this->processTemplate($originalAttribute));
                    $config = array_merge($config, $value);
                }
            }
            $config['behavior'] = $this;
            $config['originalAttribute'] = $originalAttribute;
            $config['localAttribute'] = $localAttribute;

            $this->attributeValues[$localAttribute] = Yii::createObject($config);
        }
    }

    /**
     * @inheritdoc
     */
    protected function processTemplate($originalAttribute)
    {
        return strtr($this->namingTemplate, [
            '{attribute}' => $originalAttribute,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        $events = [];
        if ($this->performValidation) {
            $events[BaseActiveRecord::EVENT_BEFORE_VALIDATE] = 'onBeforeValidate';
        }
        return $events;
    }

    /**
     * Performs validation for all the attributes
     * @param Event $event
     */
    public function onBeforeValidate($event)
    {
        foreach ($this->attributeValues as $name => $value) {

            $options = ArrayHelper::merge([
                'class' => DateValidator::className(),
                'format' => $value->localFormat[1],
            ], $this->validatorOptions);

            $validator = Yii::createObject($options);
            $validator->validateAttributes($this->owner, [$value->localAttribute]);
        }
    }

    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        if ($this->hasAttributeValue($name)) {
            return true;
        } else {
            return parent::canGetProperty($name, $checkVars);
        }
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        if ($this->hasAttributeValue($name)) {
            return true;
        } else {
            return parent::canSetProperty($name, $checkVars);
        }
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if ($this->hasAttributeValue($name)) {
            return $this->attributeValues[$name];
        } else {
            return parent::__get($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        if ($this->hasAttributeValue($name)) {
            $this->attributeValues[$name]->setValue($value);
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * @inheritdoc
     */
    protected function hasAttributeValue($name)
    {
        return isset($this->attributeValues[$name]);
    }
} 