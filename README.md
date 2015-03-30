Yii2 DateTimePicker and date format behavior
============================================
DateTimePicker widget is a wrapper of [Bootstrap DatePicker](http://eonasdan.github.io/bootstrap-datetimepicker/) for Yii 2 framework.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist maddoger/yii2-datetimepicker "*"
```

or add

```
"maddoger/yii2-datetimepicker": "*"
```

to the require section of your `composer.json` file.

Usage
-----

Once the extension is installed, simply use it in your code by:

```php
use maddoger\widgets\DateTimePicker;

echo $form->field($model, 'field')->widget(DateTimePicker::className());
```

Date Attribute
--------------

For datetime format and timezone conversation you can use `DateTimeBehavior`.

In model behaviors for timestamp field:

```php
[
    'class' => DateTimeBehavior::className(),
    'attributes' => ['published_at'],
    'originalFormat' => 'U', //original format
    'originalTimeZone' => 'UTC', //original timezone
    
    'timeZone' => 'Europe/London', //local timezone
    'format' => 'datetime', //local format, Formatter format
]
```

Now you can use `published_at` as original attribute and `published_at_local` as user read-write attribute.

For date fields:

```php
[
    'class' => DateTimeBehavior::className(),
    'attributes' => ['birth_date'],
    'originalFormat' => 'Y-m-d',
    'format' => 'date',
    'timeZone' => 'UTC',
]
```