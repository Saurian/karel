<?php

namespace CmsModule\Forms;

use CmsModule\Entities\ShopEntity;
use Nette\Forms\Form;
use Tracy\Debugger;

interface IShopFormFactory
{
    /** @return ShopForm */
    function create();
}

/**
 * Class ShopForm
 * @package CmsModule\Forms
 */
class ShopForm extends BaseForm
{

    protected $labelControlClass = 'div class="col-sm-4 control-label"';

    protected $controlClass = 'div class=col-sm-8';


    /** @return ShopForm */
    public function create()
    {
        $this->addText('name', 'name')
            ->setAttribute('placeholder', "placeholder.name")
            ->addRule(Form::FILLED, 'ruleFilled')
            ->addRule(Form::MIN_LENGTH, 'ruleMinLength', 4)
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 32);

        $this->addHidden('openTime');
        $this->addHidden('closeTime');

        $this->addText('openCloseTimeRange', 'openTime')
            ->setAttribute('data-provide', "slider-range")
            ->setAttribute('data-slider-range', "true")
            ->setAttribute('data-slider-tooltip', "always")
            ->setAttribute('data-slider-min', "4")
            ->setAttribute('data-slider-max', "22")
            ->setAttribute('data-slider-step', "1")
            ->setAttribute('data-slider-value', $this->getValueTime())
            ->setAttribute('data-custom-input-min', "openTime")
            ->setAttribute('data-custom-input-max', "closeTime")
            ->setAttribute('data-slider-orientation', "horizontal")
            ->addRule(Form::FILLED, 'ruleFilled');

        $this->addHidden('openDayOfWeek');
        $this->addHidden('closeDayOfWeek');

        $this->addText('openCloseDayRange', 'openDays')
            ->setAttribute('data-provide', "slider-range")
            ->setAttribute('data-slider-range', "true")
            ->setAttribute('data-slider-tooltip', "hide")
            ->setAttribute('data-slider-min', "1")
            ->setAttribute('data-slider-max', "7")
            ->setAttribute('data-slider-step', "1")
            ->setAttribute('data-slider-value', $this->getValueDayOfWeek())
            ->setAttribute('data-slider-ticks', "[1,2,3,4,5,6,7]")
            ->setAttribute('data-slider-ticks-labels', '["pondělí", "úterý", "středa", "čtvrtek", "pátek", "sobota", "neděle"]')
            ->setAttribute('data-slider-orientation', "horizontal")
            ->setAttribute('data-custom-input-min', "openDayOfWeek")
            ->setAttribute('data-custom-input-max', "closeDayOfWeek")
            ->addRule(Form::FILLED, 'ruleFilled');

        $this->addSubmit('send', 'save')
            ->setAttribute('data-dismiss', 'modal')
            ->setAttribute('class', 'btn btn-success');

        $this->onSuccess[] = array($this, 'success');

        $this->addFormClass(['ajax']);
        return $this;
    }


    /**
     * @return string  "[2,5]"
     */
    private function getValueDayOfWeek()
    {
        /** @var ShopEntity $entity */
        $entity = $this->getEntity();

        $from = ($entity && $open =$entity->getOpenDayOfWeek())
            ? $open
            : 1;

        $to = ($entity && $close =$entity->getCloseDayOfWeek())
            ? $close
            : 5;

        return "[$from,$to]";
    }


    /**
     * @return string
     */
    private function getValueTime()
    {
        /** @var ShopEntity $entity */
        $entity = $this->getEntity();

        $from = ($entity && $open =$entity->getOpenTime())
            ? $open
            : 7;

        $to = ($entity && $close =$entity->getCloseTime())
            ? $close
            : 18;

        return "[$from,$to]";
    }


    public function success(BaseForm $form, $values)
    {
        $entity = $form->getEntity();

        $em = $this->getEntityMapper()->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

}