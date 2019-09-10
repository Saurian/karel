<?php


namespace CmsModule\Facades;

use CmsModule\Entities\TargetGroupEntity;
use CmsModule\Entities\TargetGroupParamEntity;
use CmsModule\Entities\TargetGroupParamValueEntity;
use CmsModule\Forms\IMetricParamFormFactory;
use CmsModule\Forms\IReachFormFactory;
use CmsModule\Forms\IShopFormFactory;
use CmsModule\Forms\ITargetGroupFormFactory;
use CmsModule\Forms\ITargetGroupParamFormFactory;
use CmsModule\Repositories\MetricParamRepository;
use CmsModule\Repositories\MetricRepository;
use CmsModule\Repositories\ShopRepository;
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

    /** @var ShopRepository */
    private $shopRepository;

    /** @var MetricRepository */
    private $metricRepository;

    /** @var MetricParamRepository */
    private $metricParamRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var ITargetGroupFormFactory */
    private $targetGroupFormFactory;

    /** @var ITargetGroupParamFormFactory */
    private $targetGroupParamFormFactory;

    /** @var IMetricParamFormFactory */
    private $metricParamFormFactory;

    /** @var IShopFormFactory */
    private $shopFormFactory;

    /** @var IReachFormFactory */
    private $reachFormFactory;


    /**
     * ReachFacade constructor.
     *
     * @param TargetGroupRepository $targetGroupRepository
     * @param ShopRepository $shopRepository
     * @param ITargetGroupFormFactory $targetGroupFormFactory
     * @param ITargetGroupParamFormFactory $targetGroupParamFormFactory
     * @param IMetricParamFormFactory $metricParamFormFactory
     * @param IShopFormFactory $shopFormFactory
     * @param IReachFormFactory $reachFormFactory
     */
    public function __construct(TargetGroupRepository $targetGroupRepository, ShopRepository $shopRepository,
                                MetricRepository $metricRepository, MetricParamRepository $metricParamRepository,
                                ITargetGroupFormFactory $targetGroupFormFactory, ITargetGroupParamFormFactory $targetGroupParamFormFactory,
                                IMetricParamFormFactory $metricParamFormFactory, IShopFormFactory $shopFormFactory, IReachFormFactory $reachFormFactory)
    {
        $this->targetGroupFormFactory = $targetGroupFormFactory;
        $this->targetGroupParamFormFactory = $targetGroupParamFormFactory;
        $this->metricRepository = $metricRepository;
        $this->metricParamFormFactory = $metricParamFormFactory;
        $this->shopFormFactory = $shopFormFactory;
        $this->reachFormFactory = $reachFormFactory;
        $this->targetGroupRepository = $targetGroupRepository;
        $this->shopRepository = $shopRepository;
        $this->metricParamRepository = $metricParamRepository;
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
     * @return ShopRepository
     */
    public function getShopRepository(): ShopRepository
    {
        return $this->shopRepository;
    }

    /**
     * @return MetricRepository
     */
    public function getMetricRepository(): MetricRepository
    {
        return $this->metricRepository;
    }


    /**
     * @return MetricParamRepository
     */
    public function getMetricParamRepository(): MetricParamRepository
    {
        return $this->metricParamRepository;
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
     * @return IShopFormFactory
     */
    public function getShopFormFactory(): IShopFormFactory
    {
        return $this->shopFormFactory;
    }

    /**
     * @return IReachFormFactory
     */
    public function getReachFormFactory(): IReachFormFactory
    {
        return $this->reachFormFactory;
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