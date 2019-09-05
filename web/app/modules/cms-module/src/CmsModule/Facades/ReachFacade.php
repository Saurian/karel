<?php


namespace CmsModule\Facades;

use CmsModule\Entities\TargetGroupEntity;
use CmsModule\Entities\TargetGroupParamEntity;
use CmsModule\Entities\TargetGroupParamValueEntity;
use CmsModule\Forms\IMetricParamFormFactory;
use CmsModule\Forms\ITargetGroupFormFactory;
use CmsModule\Forms\ITargetGroupParamFormFactory;
use CmsModule\Repositories\TargetGroupRepository;
use Kdyby\Doctrine\EntityManager;

/**
 * Class ReachFacade
 * @package CmsModule\Facades
 */
class ReachFacade
{

    /** @var TargetGroupRepository */
    private $targetGroupRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var ITargetGroupFormFactory */
    public $targetGroupFormFactory;

    /** @var ITargetGroupParamFormFactory */
    public $targetGroupParamFormFactory;

    /** @var IMetricParamFormFactory */
    public $metricParamFormFactory;


    /**
     * ReachFacade constructor.
     * @param TargetGroupRepository $targetGroupRepository
     */
    public function __construct(TargetGroupRepository $targetGroupRepository, ITargetGroupFormFactory $targetGroupFormFactory,
                                ITargetGroupParamFormFactory $targetGroupParamFormFactory, IMetricParamFormFactory $metricParamFormFactory)
    {
        $this->targetGroupFormFactory = $targetGroupFormFactory;
        $this->targetGroupParamFormFactory = $targetGroupParamFormFactory;
        $this->metricParamFormFactory = $metricParamFormFactory;
        $this->targetGroupRepository = $targetGroupRepository;
        $this->entityManager = $targetGroupRepository->getEntityManager();
    }

    /**
     * @return TargetGroupRepository
     */
    public function getTargetGroupRepository(): TargetGroupRepository
    {
        return $this->targetGroupRepository;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @return ITargetGroupFormFactory
     */
    public function getTargetGroupFormFactory(): ITargetGroupFormFactory
    {
        return $this->targetGroupFormFactory;
    }

    /**
     * @return ITargetGroupParamFormFactory
     */
    public function getTargetGroupParamFormFactory(): ITargetGroupParamFormFactory
    {
        return $this->targetGroupParamFormFactory;
    }

    /**
     * @return IMetricParamFormFactory
     */
    public function getMetricParamFormFactory(): IMetricParamFormFactory
    {
        return $this->metricParamFormFactory;
    }





    /**
     * create base TargetGroup entity
     *
     * @return TargetGroupEntity
     */
    public function createTargetGroupEntity()
    {
        $entity = new TargetGroupEntity("výchozí");

        $param1 = new TargetGroupParamEntity('pohlaví', $entity);
        $param2 = new TargetGroupParamEntity('věk', $entity);

        $entity->addParam($param1)->addParam($param2);

        $param1Value1 = new TargetGroupParamValueEntity('muži', $param1);
        $param1Value2 = new TargetGroupParamValueEntity('ženy', $param1);

        $param1->addValue($param1Value1)->addValue($param1Value2);

        $param2Value1 = new TargetGroupParamValueEntity('18-70 let', $param2);

        $param2->addValue($param2Value1);

        return $entity;
    }





}