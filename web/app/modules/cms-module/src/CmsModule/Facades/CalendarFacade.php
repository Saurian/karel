<?php


namespace CmsModule\Facades;

use CmsModule\Controls\ICalendarControlFactory;
use CmsModule\Entities\CalendarEntity;
use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\ShopEntity;
use CmsModule\Entities\UsersGroupEntity;
use CmsModule\Facades\Calendar\CalendarList;
use CmsModule\OutOfRangeException;
use CmsModule\Repositories\CalendarRepository;
use CmsModule\Repositories\CampaignRepository;
use CmsModule\Repositories\ShopRepository;
use Exception;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Translation\Translator;
use Nette\DateTime;
use Nette\SmartObject;

/**
 * Class CalendarFacade
 * @package CmsModule\Facades
 * @method onGenerated($calendarList)
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

    /** @var Translator */
    private $translator;


    /**
     * CalendarFacade constructor.
     *
     * @param EntityManager $entityManager
     * @param CalendarRepository $calendarRepository
     * @param ShopRepository $shopRepository
     * @param CampaignRepository $campaignRepository
     * @param ICalendarControlFactory $calendarControl
     * @param Translator $translator
     */
    public function __construct(EntityManager $entityManager, CalendarRepository $calendarRepository, ShopRepository $shopRepository,
                                CampaignRepository $campaignRepository, ICalendarControlFactory $calendarControl, Translator $translator)
    {
        $this->entityManager      = $entityManager;
        $this->shopRepository     = $shopRepository;
        $this->campaignRepository = $campaignRepository;
        $this->calendarRepository = $calendarRepository;
        $this->calendarControl    = $calendarControl;
        $this->translator         = $translator;
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
     * @param bool $compressedResult
     * @return CalendarEntity[]
     * @throws Exception
     */
    public function generateByUsersGroup(UsersGroupEntity $usersGroupEntity, $compressedResult = true)
    {
        $this->clearCalendar($usersGroupEntity);

        /** @var CampaignEntity[] $campaigns */
        $campaigns = $this->entityManager->getRepository(CampaignEntity::class)->findBy(['usersGroups' => $usersGroupEntity, 'active' => true]);

        return $this->generateByCampaigns($usersGroupEntity, $campaigns, $compressedResult);
    }


    /**
     * @param UsersGroupEntity $usersGroupEntity
     * @param CampaignEntity[] $campaigns
     * @param bool $compressedResult
     * @return CalendarEntity[]
     * @throws Exception
     */
    public function generateByCampaigns(UsersGroupEntity $usersGroupEntity, array $campaigns, $compressedResult = true)
    {
        /** @var ShopEntity $shop */
        $shop = $this->entityManager->getRepository(ShopEntity::class)->find(1);

        $calendarList = new CalendarList();

        foreach ($campaigns as $campaign) {

            $time = $campaign->getRealizedFrom();
            $time->setTime($time->format('H'), 0, 0);
            while ($time <= $campaign->getRealizedTo()) {

                if ($this->shopRepository->isTimeInShopTimeRange($time, $shop)) {

                    if ($this->campaignRepository->isTimeInMetricsTimeRange($time, $campaign)) {

                        $percentage = $this->campaignRepository->getPercentageUse($time, $campaign);

                        $toTime = clone $time;
                        $toTime->modify('+1 hour');

                        try {
                            $calendarList->addRecord($this->getRecord($campaign, $usersGroupEntity, DateTime::from($time), DateTime::from($toTime), $percentage));

                        } catch (OutOfRangeException $e) {

                        }
                    }
                }

                $time->modify('+1 hour');
            }
        }

        if ($calendarList->hasCalendar()) {
            $calendarList = $compressedResult
                ? $calendarList->getCompressedCalendar()
                : $calendarList->getCalendar();

            $this->onGenerated($calendarList);
            $this->entityManager->persist($calendarList)->flush();
        }

        return $calendarList;
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


    /**
     * add record to calendar
     *
     * @param CampaignEntity $campaignEntity
     * @param UsersGroupEntity $usersGroupEntity
     * @param DateTime $from
     * @param DateTime|null $to
     * @param int $percentage
     * @param bool $force modify limit realizedFrom and realizedTo if need
     * @return CalendarEntity
     * @throws OutOfRangeException
     * @throws Exception
     */
    public function getRecord(CampaignEntity $campaignEntity, UsersGroupEntity $usersGroupEntity, DateTime $from, DateTime $to = null, int $percentage = 0, $force = false)
    {
        if (!$to) {
            $to = clone $from;
            $to->modify("+1 hour");
        }

        if ($force) {
            if ($from < $campaignEntity->getRealizedFrom()) {
                $campaignEntity->setRealizedFrom($from);
            }
            if ($to > $campaignEntity->getRealizedTo()) {
                $campaignEntity->setRealizedTo($to);
            }
        }

        if ($from < $campaignEntity->getRealizedFrom()) {
            throw new OutOfRangeException($this->translator->translate("messages.campaignPage.calendar.addError", null, [
                'name' => $campaignEntity->getName(),
                'datetime' => $from,
                'from' => $campaignEntity->getRealizedFrom()->format("j. n. Y H:i"),
                'to' => $campaignEntity->getRealizedTo()->format("j. n. Y H:i"),
            ]));
        }
        if ($to > $campaignEntity->getRealizedTo()) {
            throw new OutOfRangeException($this->translator->translate("messages.campaignPage.calendar.addError", null, [
                'name' => $campaignEntity->getName(),
                'datetime' => $from,
                'from' => $campaignEntity->getRealizedFrom()->format("j. n. Y H:i"),
                'to' => $campaignEntity->getRealizedTo()->format("j. n. Y H:i"),
            ]));
        }

        $entity = new CalendarEntity($campaignEntity, $usersGroupEntity, $from, $to, $percentage);
        return $entity;
    }


}