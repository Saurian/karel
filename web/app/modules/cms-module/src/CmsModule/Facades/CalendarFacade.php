<?php


namespace CmsModule\Facades;

use CmsModule\Controls\ICalendarControlFactory;
use CmsModule\Entities\CalendarEntity;
use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\ShopEntity;
use CmsModule\Entities\UsersGroupEntity;
use CmsModule\Repositories\CalendarRepository;
use CmsModule\Repositories\CampaignRepository;
use CmsModule\Repositories\ShopRepository;
use Exception;
use Kdyby\Doctrine\EntityManager;
use Nette\DateTime;
use Nette\SmartObject;

/**
 * Class CalendarFacade
 * @package CmsModule\Facades
 * @method onGenerated()
 */
class CalendarFacade
{
    use SmartObject;

    public $onGenerated = [];


    /** @var EntityManager */
    private $entityManager;

    /** @var CalendarRepository */
    private $calendarRepository;

    /** @var ShopRepository */
    private $shopRepository;

    /** @var CampaignRepository */
    private $campaignRepository;

    /** @var ICalendarControlFactory */
    private $calendarControl;

    /**
     * CalendarFacade constructor.
     *
     * @param EntityManager $entityManager
     * @param CalendarRepository $calendarRepository
     * @param ShopRepository $shopRepository
     * @param CampaignRepository $campaignRepository
     * @param ICalendarControlFactory $calendarControl
     */
    public function __construct(EntityManager $entityManager, CalendarRepository $calendarRepository, ShopRepository $shopRepository,
                                CampaignRepository $campaignRepository, ICalendarControlFactory $calendarControl)
    {
        $this->entityManager = $entityManager;
        $this->shopRepository = $shopRepository;
        $this->campaignRepository = $campaignRepository;
        $this->calendarRepository = $calendarRepository;
        $this->calendarControl = $calendarControl;
    }

    /**
     * @return CalendarRepository
     */
    public function getCalendarRepository(): CalendarRepository
    {
        return $this->calendarRepository;
    }

    /**
     * @return ShopRepository
     */
    public function getShopRepository(): ShopRepository
    {
        return $this->shopRepository;
    }

    /**
     * @return CampaignRepository
     */
    public function getCampaignRepository(): CampaignRepository
    {
        return $this->campaignRepository;
    }

    /**
     * @return ICalendarControlFactory
     */
    public function getCalendarControl(): ICalendarControlFactory
    {
        return $this->calendarControl;
    }


    /**
     * @param UsersGroupEntity $usersGroupEntity
     * @throws Exception
     */
    public function generate(UsersGroupEntity $usersGroupEntity)
    {
        $this->clearCalendar($usersGroupEntity);

        /** @var CampaignEntity[] $campaigns */
        $campaigns = $this->entityManager->getRepository(CampaignEntity::class)->findBy(['usersGroups' => $usersGroupEntity]);

        /** @var ShopEntity $shop */
        $shop = $this->entityManager->getRepository(ShopEntity::class)->find(1);

        $recorded = false;

        foreach ($campaigns as $campaign) {

            if (!$campaign->isActive()) continue;
            $time = $campaign->getRealizedFrom();
            $time->setTime($time->format('H'), 0, 0);
            while ($time <= $campaign->getRealizedTo()) {

                if ($this->shopRepository->isTimeInShopTimeRange($time, $shop)) {

                    if ($this->campaignRepository->isTimeInMetricsTimeRange($time, $campaign)) {

                        $percentage = $this->campaignRepository->getPercentageUse($time, $campaign);

                        $toTime = clone $time;
                        $toTime->modify('+1 hour');

                        $this->addRecord($campaign, $usersGroupEntity, DateTime::from($time), DateTime::from($toTime), $percentage);
                        $recorded = true;
                    }
                }

                $time->modify('+1 hour');
            }
        }

        if ($recorded) {
            $this->onGenerated();
            $this->entityManager->flush();
        }
    }


    /**
     * @param UsersGroupEntity $usersGroupEntity
     * @throws Exception
     */
    public function clearCalendar(UsersGroupEntity $usersGroupEntity)
    {
        $query = $this->calendarRepository->createQueryBuilder()
            ->delete(CalendarEntity::class, 'e')
            ->where('e.usersGroups = ?1')->setParameter(1, $usersGroupEntity);

        $query->getQuery()->execute();
    }


    public function addRecord(CampaignEntity $campaignEntity, UsersGroupEntity $usersGroupEntity, DateTime $dateTime, DateTime $length = null, int $percentage = 0)
    {
        $entity = new CalendarEntity($campaignEntity, $usersGroupEntity, $dateTime, $percentage);
        if ($length) {
            $entity->setTo($length);
        }
        $this->entityManager->persist($entity);
    }


}