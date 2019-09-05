<?php
/**
 * This file is part of the smart-up
 * Copyright (c) 2016
 *
 * @file    DateTimeInput.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Forms\Controls;

use Nette;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

class DateTimeInput extends BaseControl
{

    const TYPE_DATE = 'date';
    const TYPE_TIME = 'time';
    const TYPE_DATETIME = 'date time';

    const FILLED = 'CmsModule\Forms\Controls\DateTimeInput::validateFilled';
    const MAX_OR_EQUAL = 'CmsModule\Forms\Controls\DateTimeInput::validateMaxOrEqual';
    const MIN_OR_EQUAL = 'CmsModule\Forms\Controls\DateTimeInput::validateMinOrEqual';

    /** @var array */
    public static $days = ["" => 'den', 1 => "1.", "2.", "3.", "4.", "5.", "6.", "7.", "8.", "9.", "10.", "11.", "12.", "13.", "14.", "15.", "16.", "17.", "18.", "19.", "20.", "21.", "22.", "23.", "24.", "25.", "26.", "27.", "28.", "29.", "30.", "31."];

    /** @var array */
    public static $months = array("" => 'měsíc', 1 => 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince');

    /** @var array */
    public $years = [];

    /** @var array */
    public static $jsFormat = array(
        'd' => "dd",
        'j' => "d",
        'm' => "mm",
        'n' => "m",
        'z' => "o",
        'Y' => "yy",
        'y' => "y",
        'U' => "@",
        'h' => "h",
        'H' => "hh",
        'g' => "g",
        'A' => "TT",
        'i' => "mm",
        's' => "ss",
        'G' => "h",
    );

    /** @var string */
    public $type = self::TYPE_DATETIME;

    /** @var array */
    public $format = array(
        'time' => 'G:i',
        'date' => 'j.n.Y'
    );

    /** @var array */
    public $size = array(
        'time' => 4,
        'date' => 10,
    );

    /** @var array */
    public $class = array(
        'time' => 'input-mini',
        'date' => 'input-small',
    );

    /** @var int */
    private $day, $month, $year;

    /** @var \Datetime */
    private $datetime;

    /** @var \Datetime */
    private $minDate;

    /** @var \Datetime */
    private $maxDate;

    /**
     * @param string $caption
     */
    public function __construct($caption = NULL)
    {
        parent::__construct($caption);
        $this->addRule(__CLASS__ . '::validateDate', 'Date is invalid.');
    }

    /**
     * @param \Datetime $minDate
     *
     * @return $this
     */
    public function setMinDate($minDate)
    {
        $this->minDate = $minDate;
        return $this;
    }

    /**
     * @param \Datetime $maxDate
     *
     * @return $this
     */
    public function setMaxDate($maxDate)
    {
        $this->maxDate = $maxDate;
        return $this;
    }

    public function setValue($value)
    {
        if ($value) {
            $date        = Nette\Utils\DateTime::from($value);
            $this->day   = $date->format('j');
            $this->month = $date->format('n');
            $this->year  = $date->format('Y');
        } else {
            $this->day = $this->month = $this->year = NULL;
        }
    }

    /**
     * @return \DateTime|NULL
     */
    public function getValue()
    {
        return self::validateDate($this)
            ? date_create()->setDate($this->year, $this->month, $this->day)
            : NULL;
    }

    public function loadHttpData()
    {
        $this->day   = $this->getHttpData(Nette\Forms\Form::DATA_LINE, '[day]');
        $this->month = $this->getHttpData(Nette\Forms\Form::DATA_LINE, '[month]');
        $this->year  = $this->getHttpData(Nette\Forms\Form::DATA_LINE, '[year]');
    }

    /**
     * Generates control's HTML element.
     */
    public function getControl()
    {
        $control = parent::getControl();
        $rules   = json_encode($control->attrs['data-nette-rules']);

        $name   = $this->getHtmlName();
        $result = Html::el('div')->addAttributes(['class' => 'input-date',]);

        $selectDay   = $this->day ?: '';
        $selectMonth = $this->month ?: '';
        $selectYear  = $this->year ?: '';

        return $result
            ->add(Nette\Forms\Helpers::createSelectBox(
                self::$days,
                ['selected?' => $selectDay,'disabled:' => ['' => TRUE],]
            )->addAttributes(['class' => 'form-control input-date-item', 'placeholder'=> 'den', 'data-nette-rules' => $rules])->name($name . '[day]'))
            ->add(Nette\Forms\Helpers::createSelectBox(
                self::$months,
                ['selected?' => $selectMonth,'disabled:' => ['' => TRUE],]
            )->addAttributes(['class' => 'form-control input-date-item', 'data-nette-rules' => $rules])->name($name . '[month]'))
            ->add(Nette\Forms\Helpers::createSelectBox(
                $this->getYears(),
                ['selected?' => $selectYear,'disabled:' => ['' => TRUE],]
            )->addAttributes(['class' => 'form-control input-date-item', 'data-nette-rules' => $rules])->name($name . '[year]'));
    }

    /**
     * @return array
     */
    public function getYears()
    {
        $from = $this->minDate
            ? Nette\Utils\DateTime::from($this->minDate)
            : new Nette\Utils\DateTime('-26 years');

        $to = $this->maxDate
            ? Nette\Utils\DateTime::from($this->maxDate)
            : new Nette\Utils\DateTime('-15 years');

        $years = ["" => "rok"];
        for ($i = $from->format("Y"); $i <= $to->format("Y"); $i++) {
            $years[intval($i)] = intval($i);
        }

        return $years;
    }




    /********************* Validation rules ************************/
    /**
     * @param \CmsModule\Forms\Controls\DateTimeInput|int|string $time1
     * @param \CmsModule\Forms\Controls\DateTimeInput|int|string $time2
     * @param string|\DateInterval                               $modify
     *
     * @return string|NULL
     */
    protected static function getDiff($time1, $time2, $modify = NULL)
    {
        if (!($time1 = static::datetimeFrom($time1)) || !($time2 = static::datetimeFrom($time2))) {
            return NULL;
        }
        if ($modify !== NULL) {
            $time2->modify($modify);
        }
        $time1->setTime(0, 0, 0);
        $time2->setTime(0, 0, 0);
        /** @var \DateInterval $diff */
        $diff = $time1->diff($time2);
        return $diff->format('%r%a');
    }

    /**
     * @param \CmsModule\Forms\Controls\DateTimeInput|int|string $time
     *
     * @return \DateTime|NULL
     */
    private static function datetimeFrom($time)
    {
        if ($time instanceof self) {
            $time = $time->datetime;
            $time = $time ? clone $time : FALSE;
        } else {
            $time = Nette\Utils\DateTime::from($time);
        }
        return $time ?: NULL;
    }

    /**
     * @return bool
     */
    public static function validateDate(DateTimeInput $control)
    {
        return ($control->month && $control->day && $control->year)
            ? checkdate($control->month, $control->day, $control->year)
            : false;
    }

    /**
     * @param \CmsModule\Forms\Controls\DateTimeInput $time1
     * @param \DateTime|int|string                    $time2
     * @param string|\DateInterval                    $modify
     *
     * @return bool
     */
    public static function validateMin(DateTimeInput $time1, $time2, $modify = NULL)
    {
        if ($time1->validateDate($time1)) {
            $controlDate = Nette\Utils\DateTime::from("{$time1->year}-{$time1->month}-{$time1->day}");
            $checkDate   = Nette\Utils\DateTime::from($time2);
            return $controlDate > $checkDate;
        }

        return false;
    }

    /**
     * @param \CmsModule\Forms\Controls\DateTimeInput $time1
     * @param \DateTime|int|string                    $time2
     * @param string|\DateInterval                    $modify
     *
     * @return bool
     */
    public static function validateMinOrEqual(DateTimeInput $time1, $time2, $modify = NULL)
    {
        if ($time1->validateDate($time1)) {
            $controlDate = Nette\Utils\DateTime::from("{$time1->year}-{$time1->month}-{$time1->day}");
            $checkDate   = Nette\Utils\DateTime::from($time2);
            return $controlDate >= $checkDate;
        }

        return false;
    }

    /**
     * @param \CmsModule\Forms\Controls\DateTimeInput $time1
     * @param \DateTime|int|string                    $time2
     * @param string|\DateInterval                    $modify
     *
     * @return bool
     */
    public static function validateMaxOrEqual(DateTimeInput $time1, $time2, $modify = NULL)
    {
        if ($time1->validateDate($time1)) {
            $controlDate = Nette\Utils\DateTime::from("{$time1->year}-{$time1->month}-{$time1->day}");
            $checkDate   = Nette\Utils\DateTime::from($time2);
            return $controlDate <= $checkDate;
        }

        return false;
    }

    /**
     * @param \CmsModule\Forms\Controls\DateTimeInput $time1
     * @param \DateTime|int|string                    $time2
     * @param string|\DateInterval                    $modify
     *
     * @return bool
     */
    public static function validateMax(DateTimeInput $time1, $time2, $modify = NULL)
    {
        if ($time1->validateDate($time1)) {
            $controlDate = Nette\Utils\DateTime::from("{$time1->year}-{$time1->month}-{$time1->day}");
            $checkDate   = Nette\Utils\DateTime::from($time2);
            return $controlDate < $checkDate;
        }

        return false;
    }

    /**
     * @param \CmsModule\Forms\Controls\DateTimeInput $control
     *
     * @return bool
     */
    public static function validateValidDate(DateTimeInput $control)
    {
        return !$control->hasErrors();
    }

    /**
     * @param \Nette\Forms\Controls\BaseControl|\Nette\Forms\IControl $control
     *
     * @return bool
     */
    public static function validateFilled(Nette\Forms\IControl $control)
    {
        return $control->day && $control->month && $control->year; // NULL, FALSE, '' ==> FALSE
    }

    /**
     * Registers methods addDate, addTime & addDatetime to form Container class.
     */
    public static function register()
    {
        Container::extensionMethod('addDate', function (Container $container, $name, $label = NULL) {
            $control       = new DateTimeInput($label);
            $control->type = DateTimeInput::TYPE_DATE;
            return $container[$name] = $control;
        });
        /*
        Container::extensionMethod('addTime', function (Container $container, $name, $label = NULL) {
            $control       = new DateTimeInput($label);
            $control->type = DateTimeInput::TYPE_TIME;
            return $container[$name] = $control;
        });
        Container::extensionMethod('addDatetime', function (Container $container, $name, $label = NULL) {
            $control       = new DateTimeInput($label);
            $control->type = DateTimeInput::TYPE_DATETIME;
            return $container[$name] = $control;
        });
        */
    }


}