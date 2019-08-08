<?php
/**
 * This file is part of the smart-up
 * Copyright (c) 2016
 *
 * @file    BootstrapDatePicker.php
 * @author  Pavel Paulík <pavel.paulik1@gmail.com>
 */

namespace CmsModule\Forms\Controls;

use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\DateTime;
use Nette\Utils\Html;
use Tracy\Debugger;

class BootstrapDateRangePicker extends BaseControl implements IRangeControl
{

    /** Default language */
    const DEFAULT_LANGUAGE = 'en';

    /** Defaut date format */
    const W3C_DATE_FORMAT = 'yyyy.mm.dd';

    /** Defaut smart date format */
    const SMART_DATE_FORMAT = 'j. n. Y';

    /** Defaut smart date format */
    const SMART_DATE_TIME_FORMAT = 'j. n. Y H:i';

    /** Defaut date format */
    const JS_FORMAT = 'd/m/Y';

    const DATE_RANGE = 'CmsModule\Forms\Controls\BootstrapDateRangePicker::validateDateRange';

    const DATETIME_RANGE = 'CmsModule\Forms\Controls\BootstrapDateRangePicker::validateDateTimeRange';

    /** @var     string                        date format    Date format - d, m, M, y */
    private $format = self::SMART_DATE_FORMAT;

    private $phpFormat = 'd.m.Y';

    private $phpTimeFormat = 'd.m.Y H:i';

    /** @var     number                        Definition of first day of default start */
    public $modifyStartDay = '+4 weeks';

    /** @var     string                    Language */
    private $language = self::DEFAULT_LANGUAGE;

    /** @var int */
    private $fromYear, $fromMonth, $fromDay;

    /** @var int */
    private $toYear, $toMonth, $toDay;

    /** @var DateTime */
    private $minDate;

    /** @var DateTime */
    private $maxDate;

    /** @var bool */
    private $timePicker = false;

    /** @var DateTime */
    private $fromValue;

    /** @var DateTime */
    private $toValue;

    /** @var string */
    private $toName;

//    private $outClass = 'input-group default-daterange width-full';
    private $outClass = 'input-daterange-timepicker';

    private $inputClass = 'form-control';


    /**
     * @param string $format
     * @param string $language
     * @param null   $label
     */
    public function __construct($format = self::SMART_DATE_FORMAT, $language = self::DEFAULT_LANGUAGE, $label = null)
    {
        parent::__construct($label);
//        $this->addRule(__CLASS__ . '::validateDate', 'Date is invalid.');
        $this->control->type = 'text';
        $this->format        = $format;
        $this->language      = $language;
    }

    /**
     * @param string $format
     * @param string $language
     * @param string $method
     */
    public static function register($format = self::SMART_DATE_FORMAT, $language = 'en', $method = 'addDateRangePicker')
    {
        $class = function_exists('get_called_class') ? get_called_class() : __CLASS__;
        \Nette\Forms\Container::extensionMethod(
            $method, function (\Nette\Forms\Container $container, $name, $label = null) use ($class, $format, $language) {
            return $container[$name] = new $class($format, $language, $label);
        }
        );

    }

    /**
     * @param string $outClass
     *
     * @return $this
     */
    public function setOutClass($outClass)
    {
        $this->outClass = $outClass;
        return $this;
    }


    public function _loadHttpData()
    {
        if ($stringDateRange = $this->getHttpData(Form::DATA_TEXT)) {
            if ($arrayValues = $this->parseRangeValueFromString($stringDateRange)) {
                $this->fromValue = $arrayValues[0];
                $this->toValue   = $arrayValues[1];
            }
        }
    }


    /**
     * @param string|null $modify
     *
     * @return DateTime
     */
    private function getDefaultDate($modify = null)
    {
        $defDate = max(new DateTime($modify), $this->minDate);
        $defDate = min($defDate, $this->maxDate);
        return $defDate;
    }


    public function setValue($value)
    {
        if ($value) {

            if (is_array($value) && count($value) == 2) {
                if (null == $value[0]) {
                    $value[0] = $this->getDefaultDate();
                }
                if (null == $value[1]) {
                    $value[1] = $this->getDefaultDate($this->modifyStartDay);
                }

                $date            = \Nette\Utils\DateTime::from($value[0]);
                $this->fromDay   = $date->format('d');
                $this->fromMonth = $date->format('m');
                $this->fromYear  = $date->format('Y');
                $this->fromValue = $date;

                $date          = \Nette\Utils\DateTime::from($value[1]);
                $this->toDay   = $date->format('d');
                $this->toMonth = $date->format('m');
                $this->toYear  = $date->format('Y');
                $this->toValue = $date;

            } elseif (is_scalar($value)) {
                if (!$arrayValues = $this->parseRangeValueFromString($value)) {
                    return false;
                }

                $this->fromValue = $arrayValues[0];
                $this->toValue   = $arrayValues[1];
            }

        } else {
            $this->fromYear = $this->fromMonth = $this->fromDay = $this->toYear = $this->toMonth = $this->toDay = null;
        }

        return true;
    }


    /**
     * parse 12. 02. 1558 - 24. 05. 2015
     *
     * @param $value
     *
     * @return array|bool
     */
    private function parseRangeValueFromString($value)
    {
        $parse         = explode('-', $value);
        if (count($parse) == 2) {
            $parseDateFrom = trim($this->parseDate($parse[0]));
            $parseDateTo   = trim($this->parseDate($parse[1]));

            try {
                $dateFrom = $this->timePicker
                    ? DateTime::createFromFormat($this->format, $parseDateFrom)
                    : DateTime::createFromFormat($this->format, $parseDateFrom)->setTime(0, 0, 0);

                $dateTo   = $this->timePicker
                    ? DateTime::createFromFormat($this->format, $parseDateTo)
                    : DateTime::createFromFormat($this->format, $parseDateTo)->setTime(0, 0, 0);

            } catch (\Exception $exc) {
                return false;
            }

            return [$dateFrom, $dateTo];
        }

        return false;
    }


    public function getValue()
    {
        self::validateDate($this);
        return [$this->fromValue, $this->toValue];
    }

    /**
     * @param string $class
     *
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }


    /**
     *  <div class="input-group default-daterange" id="default-daterange">
     *    <input type="text" name="default-daterange" class="form-control" value="" placeholder="click to select the date range"/>
     *    <span class="input-group-btn">
     *      <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
     *    </span>
     *  </div>
     *
     * @return Html|string
     */
    public function getControl()
    {
        $name      = $this->getHtmlName();
        $control   = parent::getControl();
        $class     = isset($control->attrs['class']) ? $control->attrs['class'] : $this->inputClass;
        $fromValue = DateTime::from($this->fromValue)->format($this->format);
        $toValue   = DateTime::from($this->toValue)->format($this->format);
        //$value = $this->encodeDate($fromValue) . " - " . $this->encodeDate($toValue);
        $value = $fromValue . " - " . $toValue;

        $attributes = [
            'class'       => $class,
            'value'       => $value,
            'placeholder' => 'Vyplňte prosím datum',
        ];

        if ($fromValue = $this->fromValue) {
            $attributes['data-date-start'] = DateTime::from($fromValue)->format($this->format);
        }
        if ($toDate = $this->toValue) {
            $attributes['data-date-end'] = DateTime::from($toDate)->format($this->format);
        }
        if ($minDate = $this->minDate) {
            $attributes['data-date-min'] = DateTime::from($minDate)->format($this->format);
        }
        if ($maxDate = $this->maxDate) {
            $attributes['data-date-max'] = DateTime::from($maxDate)->format($this->format);
        }
        if ($this->timePicker) {
            $attributes['data-time-picker'] = "true";
            $attributes['data-time-picker-24'] = "true";
            $attributes['data-time-picker-increment'] = 1;
        }

        return Html::el()
//            ->addHtml(Html::el('div')->addAttributes(['class' => $this->outClass])
                ->addHtml($control->value($value)->addAttributes($attributes)->addAttributes(['class' => $this->outClass . ' '  . $this->inputClass])
//                ->addHtml(Html::el('span')->addAttributes(['class' => 'input-group-btn'])
//                    ->addHtml(Html::el('span')->addAttributes(['class' => 'btn btn-default disabled'])->addHtml(Html::el('i')->addAttributes(['class' => 'fa fa-calendar']))))

//        return Html::el()
//            ->addHtml(Html::el('div')->addAttributes(['class' => $this->outClass])
//                ->addHtml($control->value($value)->addAttributes($attributes))
//                ->addHtml(Html::el('span')->addAttributes(['class' => 'input-group-btn'])
//                    ->addHtml(Html::el('span')->addAttributes(['class' => 'btn btn-default disabled'])->addHtml(Html::el('i')->addAttributes(['class' => 'fa fa-calendar']))))
            );
    }


    /**
     * set atributu
     *
     * @param & $attributes
     * @param $name
     */
    private function generateControlAttribute(& $attributes, $name)
    {
        $controlAttributes = $this->control->attrs;
        if (isset($controlAttributes[$name])) {
            $attributes[$name] = $controlAttributes[$name];
        }
    }


    /**
     * @deprecated use getControl instead (tato metoda by vyžadovala použití post)
     * @return Html
     */
    public function getControlTwoInput()
    {
        $name       = $this->getHtmlName();
        $value      = DateTime::from($this->fromValue)->format($this->phpFormat);
        $rangeValue = DateTime::from($this->rangeValue)->format($this->phpFormat);

        return Html::el()
            ->add(Html::el('div')->addAttributes(['class' => $this->outClass])
                ->add(Html::el('input')->addAttributes([
                    'class'              => $this->inputClass,
                    'data-date-language' => $this->language,
                    'data-date-format'   => $this->format
                ])->name($name)->id($this->getHtmlId())->value($value))
                ->add(Html::el('span')->addAttributes(['class' => 'input-group-addon'])->setText('To'))
                ->add(Html::el('input')->addAttributes(['class' => $this->inputClass])->name('realizedTo')->value($rangeValue))
            );
    }


    /**
     * @return bool
     */
    public static function validateDate(IRangeControl $control)
    {
        return
            checkdate($control->fromMonth, $control->fromDay, $control->fromYear) &&
            checkdate($control->toMonth, $control->toDay, $control->toYear);
    }

    /**
     * @return bool
     */
    public static function validateDateRange(IRangeControl $control, $args)
    {
        if (is_array($args) && count($args) == 2) {
            return ($args[0] > $control->getFromValue() || $args[1] < $control->getToValue()) == false;
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function validateDateTimeRange(IRangeControl $control, $args)
    {
        if (is_array($args) && count($args) == 2) {
            return ($args[0] > $control->getFromValue() || $args[1] < $control->getToValue()) == false;
        }

        return false;
    }

    private function getAj()
    {
        return array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

    }

    private function getCz()
    {
        return array("leden", "únor", "březen", "duben", "květen", "červen", "červenec", "srpen", "září", "říjen", "listopad", "prosinec");

    }

    private function parseDate($date)
    {
        $aj = $this->getAj();
        $cz = $this->getCz();
        return str_replace($cz, $aj, $date);
    }

    private function encodeDate($date)
    {
        $_date = DateTime::from($date)->format('j. F Y');
        $aj    = $this->getAj();
        $cz    = $this->getCz();
        return str_replace($aj, $cz, $_date);
    }

    /**
     * @return DateTime
     */
    public function getFromValue()
    {
        return $this->fromValue;
    }


    /**
     * Sets control's range value.
     *
     * @param  mixed
     *
     * @return void
     */
    function setToValue($value)
    {
        if ($value) {
            $date          = \Nette\Utils\DateTime::from($value);
            $this->toDay   = $date->format('d');
            $this->toMonth = $date->format('m');
            $this->toYear  = $date->format('Y');
        }
    }

    /**
     * Returns control's range value.
     *
     * @return mixed
     */
    function getToValue()
    {
        return $this->toValue;
    }

    /**
     * @param string $toName
     *
     * @return $this
     */
    public function setToName($toName)
    {
        $this->toName = $toName;
        return $this;
    }

    /**
     * Returns control's range name.
     *
     * @return string
     */
    function getToName()
    {
        return $this->toName;
    }

    /**
     * @param \DateTime $minDate
     *
     * @return $this
     */
    public function setMinDate(\DateTime $minDate)
    {
        $this->minDate = $minDate;
        return $this;
    }

    /**
     * @param \DateTime $maxDate
     *
     * @return $this
     */
    public function setMaxDate(\DateTime $maxDate)
    {
        $this->maxDate = $maxDate;
        return $this;
    }

    /**
     * @param $timePicker
     *
     * @return $this
     */
    public function setTimePicker($timePicker)
    {
        $this->timePicker = $timePicker;
        $this->format = self::SMART_DATE_TIME_FORMAT;
        return $this;
    }

    /**
     * @param string $inputClass
     *
     * @return $this
     */
    public function setInputClass($inputClass)
    {
        $this->inputClass = $inputClass;
        return $this;
    }


}