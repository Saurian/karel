<?php


namespace CmsModule\Facades;

use CmsModule\Controls\ICalendarControlFactory;
use CmsModule\Entities\CalendarEntity;
use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\ShopEntity;
use CmsModule\Entities\UsersGroupEntity;
use CmsModule\Facades\Calendar\CalendarList;
use CmsModule\Facades\Calendar\Generator;
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

    /** @var array */
    private $generatorOptions = [];

    /**
     * CalendarFacade constructor.
     *
     * @param array $generatorOptions
     * @param EntityManager $entityManager
     * @param CalendarRepository $calendarRepository
     * @param ShopRepository $shopRepository
     * @param CampaignRepository $campaignRepository
     * @param ICalendarControlFactory $calendarControl
     * @param Translator $translator
     */
    public function __construct(array $generatorOptions, EntityManager $entityManager, CalendarRepository $calendarRepository, ShopRepository $shopRepository,
                                CampaignRepository $campaignRepository, ICalendarControlFactory $calendarControl, Translator $translator)
    {
        $this->generatorOptions   = $generatorOptions;
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
     * @return Generator
     */
    public function generator()
    {
        return (new Generator($this->entityManager, $this->translator))->setStrategyFragment($this->generatorOptions['strategy']['calendarFragment']);
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
     *
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