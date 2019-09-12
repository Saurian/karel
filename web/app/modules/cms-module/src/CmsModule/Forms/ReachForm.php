<?php


namespace CmsModule\Forms;

use CmsModule\Entities\MetricEntity;
use CmsModule\Entities\MetricParamEntity;
use CmsModule\Entities\ShopEntity;
use CmsModule\Entities\TargetGroupEntity;
use CmsModule\Entities\UsersGroupEntity;
use Devrun\Doctrine\DoctrineForms\IComponentMapper;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Kdyby\Translation\Phrase;
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

    protected $formClass = ['form'];

    protected $labelControlClass = 'div class="control-label"';

    protected $controlClass = 'div class=control-input';

    /** @var ShopEntity[] */
    private $shops = [];

    /** @var TargetGroupEntity[] */
    private $targetGroups = [];

    /** @var MetricParamEntity[] */
    private $metricParams = [];

    /** @var UsersGroupEntity */
    private $userGroup;


    /**
     * @return ReachForm
     */
    public function create()
    {
        $this->addText('name', 'name')
            ->setAttribute('placeholder', "placeholder.name")
            ->addRule(Form::FILLED, 'ruleFilled')
            ->addRule(Form::MIN_LENGTH, 'ruleMinLength', 3)
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 255);

        $this->addSelect('shop', $this->getTranslator()->translate('shop'), $this->shops)
            ->setTranslator(null)
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name')
            ->setOption(IComponentMapper::ITEMS_FILTER, ['usersGroup' => $this->userGroup])
            ->addRule(Form::FILLED, 'ruleFilled');

        $this->addSelect('targetGroup', $this->getTranslator()->translate('targetGroup'), $this->targetGroups)
            ->setTranslator(null)
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name')
            ->setOption(IComponentMapper::ITEMS_FILTER, ['usersGroup' => $this->userGroup])
            ->addRule(Form::FILLED, 'ruleFilled');

        $this->addSelect('metricParam', $this->getTranslator()->translate('metricParam'), $this->metricParams)
            ->setTranslator(null)
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name')
            ->setOption(IComponentMapper::ITEMS_FILTER, ['usersGroup' => $this->userGroup])
            ->addRule(Form::FILLED, 'ruleFilled');

        $this->addSubmit('send', 'send')
            ->setAttribute('data-dismiss', 'modal')
            ->setAttribute('class', 'btn btn-success');

        $this->onSuccess[] = array($this, 'success');

        $this->addFormClass(['ajax']);
        return $this;
    }


    public function success(BaseForm $form, $values)
    {
        /** @var MetricEntity $entity */
        $entity = $form->getEntity();

        try {
            $em = $this->getEntityMapper()->getEntityManager();
            $em->persist($entity);
            $em->flush();

        } catch (UniqueConstraintViolationException $e) {
            $form->addError(new Phrase('uniqueRecord', null, [
                'shop' => $entity->getShop()->getName(),
                'targetGroup' => $entity->getTargetGroup()->getName(),
                'metricParam' => $entity->getMetricParam()->getName(),
            ]));
        }
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

    /**
     * @param UsersGroupEntity $userGroup
     * @return ReachForm
     */
    public function setUserGroup(UsersGroupEntity $userGroup): ReachForm
    {
        $this->userGroup = $userGroup;
        return $this;
    }




}