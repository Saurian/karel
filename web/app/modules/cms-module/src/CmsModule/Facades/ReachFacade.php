<?php


namespace CmsModule\Facades;

use CmsModule\Entities\TargetGroupParamEntity;
use CmsModule\Entities\TargetGroupParamValueEntity;
use CmsModule\Entities\UserEntity;
use CmsModule\Forms\IMetricParamFormFactory;
use CmsModule\Forms\IReachFormFactory;
use CmsModule\Forms\IShopFormFactory;
use CmsModule\Forms\ITargetGroupFormFactory;
use CmsModule\Forms\ITargetGroupParamFormFactory;
use CmsModule\Repositories\MetricParamRepository;
use CmsModule\Repositories\MetricRepository;
use CmsModule\Repositories\MetricStatisticRepository;
use CmsModule\Repositories\ShopRepository;
use CmsModule\Repositories\TargetGroupParamValueRepository;
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

    /** @var TargetGroupParamValueRepository @inject */
    public $targetGroupParamValueRepository;

    /** @var ShopRepository */
    private $shopRepository;

    /** @var MetricRepository */
    private $metricRepository;

    /** @var MetricParamRepository */
    private $metricParamRepository;

    /** @var MetricStatisticRepository */
    private $metricStatisticRepository;

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
     * @param MetricRepository $metricRepository
     * @param MetricParamRepository $metricParamRepository
     * @param MetricStatisticRepository $metricStatisticRepository
     * @param ITargetGroupFormFactory $targetGroupFormFactory
     * @param ITargetGroupParamFormFactory $targetGroupParamFormFactory
     * @param IMetricParamFormFactory $metricParamFormFactory
     * @param IShopFormFactory $shopFormFactory
     * @param IReachFormFactory $reachFormFactory
     */
    public function __construct(TargetGroupRepository $targetGroupRepository, ShopRepository $shopRepository, MetricRepository $metricRepository,
                                MetricParamRepository $metricParamRepository, MetricStatisticRepository $metricStatisticRepository,
                                ITargetGroupFormFactory $targetGroupFormFactory, ITargetGroupParamFormFactory $targetGroupParamFormFactory,
                                IMetricParamFormFactory $metricParamFormFactory, IShopFormFactory $shopFormFactory, IReachFormFactory $reachFormFactory)
    {
        $this->targetGroupFormFactory = $targetGroupFormFactory;
        $this->targetGroupParamFormFactory = $targetGroupParamFormFactory;
        $this->metricRepository = $metricRepository;
        $this->metricParamFormFactory = $metricParamFormFactory;
        $this->metricStatisticRepository = $metricStatisticRepository;
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
     * @return MetricStatisticRepository
     */
    public function getMetricStatisticRepository(): MetricStatisticRepository
    {
        return $this->metricStatisticRepository;
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
     * create default target group param values
     * @return TargetGroupParamEntity[] for new admin user
     */
    public function createNewTargetGroupParamsValuesForUser(UserEntity $userEntity)
    {
        $param1 = new TargetGroupParamEntity('Pohlaví', $userEntity->getGroup());
        $param2 = new TargetGroupParamEntity('Věk', $userEntity->getGroup());

        $param1Value1 = new TargetGroupParamValueEntity('muži', $param1);
        $param1Value2 = new TargetGroupParamValueEntity('ženy', $param1);
        $param2Value1 = new TargetGroupParamValueEntity('18-70 let', $param2);

        $param1->setCreatedBy($userEntity)->setUpdatedBy($userEntity);
        $param2->setCreatedBy($userEntity)->setUpdatedBy($userEntity);

        $param1Value1->setCreatedBy($userEntity)->setUpdatedBy($userEntity);
        $param1Value2->setCreatedBy($userEntity)->setUpdatedBy($userEntity);
        $param2Value1->setCreatedBy($userEntity)->setUpdatedBy($userEntity);

        return [$param1, $param2];
    }


}