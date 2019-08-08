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

class BootstrapDatePicker extends BaseControl
{
    const VALID_DATE_RANGE = 'CmsModule\Forms\Controls\BootstrapDatePicker::validateDateRange';
    const VALID_FILLED = 'CmsModule\Forms\Controls\BootstrapDatePicker::validateFilled';
    const VALID_VALID = 'CmsModule\Forms\Controls\BootstrapDatePicker::validateValid';

    /** Range validator @deprecated use VALID_DATE_RANGE */
    const DATE_RANGE = ':dateRange';

    /** Default language */
    const DEFAULT_LANGUAGE = 'en';

    /** Defaut date format */
    const W3C_DATE_FORMAT = 'yyyy.mm.dd';

    /** Defaut smart date format */
    const SMART_DATE_FORMAT = 'j. n. Y';

    /** Defaut smart date format */
    const SMART_DATE_TIME_FORMAT = 'j. n. Y H:i';

    /** @var     string                        date format    Date format - d, m, M, y */
    private $format = self::SMART_DATE_FORMAT;

    private $phpFormat = 'd.m.Y';

    private $phpTimeFormat = 'd.m.Y H:i';

    /** @var     number                        Definition of first day of week. 0 for Sunday, 6 for Saturday */
    private $weekStart = 1;

    /** @var     string                    Language */
    private $language = self::DEFAULT_LANGUAGE;

    /** @var int */
    private $year, $month, $day;

    /** @var DateTime */
    private $rawValue;

    private $class = 'form-control date';

    private $classOut = 'input-group _date ';

    /** @var DateTime */
    private $minDate, $maxDate;

    /** @var bool */
    private $timePicker = false;

    /** @var bool */
    private $todayButton = false;

    /** @var bool pokud není datum nastaven, použije se aktuální datum? */
    private $todayValue = false;

    /** @var bool pokud není datum nastaven, nastaví se v kalendáři datum? (maximální povolená hodnota) */
    private $viewValue = true;

    private $placeHolderValue = 'Vyplňte položku, prosím';

    /**
     * @param string $format
     * @param string $language
     * @param null   $label
     */
    public function __construct($format = self::W3C_DATE_FORMAT, $language = self::DEFAULT_LANGUAGE, $label = null)
    {
        parent::__construct($label);
        $this->addRule(__CLASS__ . '::validateDate', 'Vyplňte datum, prosím.');
        $this->control->type = 'text';
        $this->format        = $format;
        $this->language      = $language;
    }

    /**
     * @param string $format
     * @param string $language
     * @param string $method
     */
    public static function register($format = self::SMART_DATE_FORMAT, $language = 'en', $method = 'addDatePicker')
    {
        $class = function_exists('get_called_class') ? get_called_class() : __CLASS__;
        \Nette\Forms\Container::extensionMethod(
            $method, function (\Nette\Forms\Container $container, $name, $label = null) use ($class, $format, $language) {
                return $container[$name] = new $class($format, $language, $label);
            }
        );

    }


    /**
     * @todo not use
     */
    public function _loadHttpData()
    {
        if ($stringDateRange = $this->getHttpData(Form::DATA_TEXT)) {

            dump($stringDateRange);
            die();

            if ($arrayValues = $this->parseRangeValueFromString($stringDateRange)) {
                $this->fromValue = $arrayValues[0];
                $this->toValue   = $arrayValues[1];
            }
        }
    }


    public function setValue($value)
    {
        if ($value) {
            if (is_string($value)) {
                $value = DateTime::createFromFormat($this->format, $value);
            }
            $this->value = $value;

        } else {
            $this->year = $this->month = $this->day = null;
        }

    }

    public function getValue()
    {
        self::validateDate($this);
        return $this->value;
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
     * <div class="input-group date" id="datepicker-disabled-past" data-date-format="dd-mm-yyyy" data-date-start-date="Date.default">
     *  <input type="text" class="form-control" placeholder="Select Date" />
     *  <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
     * </div>
     *
     *
     * @return Html|string
     */
    public function getControl()
    {
        $control = parent::getControl();

        $value = null;
        if ($this->value) {
            $value = DateTime::from($this->value)->format($this->phpFormat);

        } else {
            $value = $this->todayValue
                ? DateTime::from($this->value)->format($this->phpFormat)
                : null;
        }

        $attributes = [
            'class'         => $this->class,
            'placeholder'   => $this->placeHolderValue,
        ];

        if ($this->minDate) {
            $attributes['data-date-min'] = $this->minDate->format('d/m/Y');
        }
        if ($this->maxDate) {
            $attributes['data-date-max'] = $this->maxDate->format('d/m/Y');
            if (null == $value && $this->todayValue) {
                $attributes['data-default-date'] = $this->maxDate->format('d/m/Y');
            }
            if (null == $value && $this->viewValue) {
                $attributes['data-view-date'] = $this->maxDate->format('d/m/Y');
            }
        }
        if ($this->todayButton) {
            $attributes['data-today-button'] = $this->todayButton;
        }
        if ($this->timePicker) {
            $attributes['data-time-picker'] = "true";
            $attributes['data-time-picker-24'] = "true";
            $attributes['data-time-picker-increment'] = 5;
        }

        $outerAttributes = [
            'data-date-autoclose' => 'true',
            'class' => $this->classOut,
        ];

        return Html::el()->add($control->value($value)->addAttributes($attributes));
    }

    /**
     * @param \DateTime $minDate
     *
     * @return $this
     */
    public function setMinDate($minDate)
    {
        $this->minDate = DateTime::from($minDate);
        return $this;
    }

    /**
     * @param \DateTime $maxDate
     *
     * @return $this
     */
    public function setMaxDate($maxDate)
    {
        $this->maxDate = DateTime::from($maxDate);
        return $this;
    }

    /**
     * @param boolean $todayButton
     *
     * @return $this
     */
    public function setTodayButton($todayButton)
    {
        $this->todayButton = (bool) $todayButton;
        return $this;
    }

    /**
     * @param boolean $todayValue
     *
     * @return $this
     */
    public function setTodayValue($todayValue)
    {
        $this->todayValue = (bool) $todayValue;
        return $this;
    }

    /**
     * @param string $placeHolderValue
     *
     * @return $this
     */
    public function setPlaceHolderValue($placeHolderValue)
    {
        $this->placeHolderValue = $placeHolderValue;
        return $this;
    }

    /**
     * @param boolean $timePicker
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
     * @return bool
     */
    public static function validateDate(\Nette\Forms\IControl $control)
    {
        return ($control->value instanceof DateTime);
    }


    public static function validateDateRange(BootstrapDatePicker $control, array $range)
    {
        if (count($range) == 2) {

            /** @var DateTime $from */
            $from = $range[0];

            /** @var DateTime $from */
            $to = $range[1];

            /** @var DateTime $value */
            $value = $control->getValue();
            if (!$control->timePicker) {
                $value->setTime(0, 0, 0);
            }

            $result = $control->getValue() >= $from && $control->getValue() <= $to;
            return $result;
        }

        return false;
    }



}