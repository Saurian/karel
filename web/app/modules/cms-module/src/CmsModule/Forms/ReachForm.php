<?php


namespace CmsModule\Forms;

use CmsModule\Entities\MetricParamEntity;
use CmsModule\Entities\ShopEntity;
use CmsModule\Entities\TargetGroupEntity;
use Nette\Forms\Form;

interface IReachFormFactory
{
    /** @return ReachForm */
    function create();
}

/**
 * Class ReachForm
 * @package CmsModule\Forms
 */
class ReachForm extends BaseForm
{

    /** @var ShopEntity[] */
    private $shops = [];

    /** @var TargetGroupEntity[] */
    private $targetGroups = [];

    /** @var MetricParamEntity[] */
    private $metricParams = [];


    /**
     * @return ReachForm
     */
    public function create()
    {
        $this->addSelect('shop', 'shop', $this->shops)
            ->addRule(Form::FILLED, 'ruleFilled');

        $this->addSelect('targetGroup', 'targetGroup', $this->targetGroups)
            ->addRule(Form::FILLED, 'ruleFilled');

        $this->addSelect('metricParam', 'metricParam', $this->metricParams)
            ->addRule(Form::FILLED, 'ruleFilled');

        $this->addSubmit('send', 'save')
//            ->setAttribute('data-dismiss', 'modal')
            ->setAttribute('class', 'btn btn-success');

        $this->onSuccess[] = array($this, 'success');

//        $this->addFormClass(['ajax']);
        return $this;
    }


    public function success(BaseForm $form, $values)
    {
        $entity = $form->getEntity();

//        dump($entity);
//        dump($values);

//        die(__METHOD__);

        $em = $this->getEntityMapper()->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }


    /**
     * @param ShopEntity[] $shops
     * @return ReachForm
     */
    public function setShops(array $shops): ReachForm
    {
        $this->shops = $shops;
        return $this;
    }

    /**
     * @param TargetGroupEntity[] $targetGroups
     * @return ReachForm
     */
    public function setTargetGroups(array $targetGroups): ReachForm
    {
        $this->targetGroups = $targetGroups;
        return $this;
    }

    /**
     * @param MetricParamEntity[] $metricParams
     * @return ReachForm
     */
    public function setMetricParams(array $metricParams): ReachForm
    {
        $this->metricParams = $metricParams;
        return $this;
    }


}