<?php


namespace CmsModule\Facades\Calendar;

use CmsModule\Entities\CalendarEntity;
use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\UsersGroupEntity;
use CmsModule\InvalidArgumentException;
use CmsModule\OutOfRangeException;
use Devrun\Utils\Time;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Translation\Translator;
use Nette\DateTime;
use Nette\SmartObject;

/**
 * Class Generator
 * @package CmsModule\Facades\Calendar
 * @method onGenerated(array $calendar)
 */
class Generator
{

    use SmartObject;

    /** @var EntityManager */
    private $entityManager;

    /** @var Translator */
    private $translator;

    /** @var CampaignEntity[] */
    private $campaigns = [];

    /** @var UsersGroupEntity */
    private $usersGroup;

    /** @var array */
    public $onGenerated = [];

    /** @var bool create fragment */
    private $strategyFragment = false;

    /**
     * Generator constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, Translator $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }


    /**
     * @param CampaignEntity[] $campaigns
     * @return Generator
     */
    public function setCampaigns(array $campaigns): Generator
    {
        $this->campaigns = $campaigns;
        return $this;
    }

    /**
     * @param bool $strategyFragment
     * @return Generator
     */
    public function setStrategyFragment(bool $strategyFragment): Generator
    {
        $this->strategyFragment = $strategyFragment;
        return $this;
    }





    /**
     * @param UsersGroupEntity $usersGroup
     * @return Generator
     */
    public function setUsersGroup(UsersGroupEntity $usersGroup): Generator
    {
        $this->usersGroup = $usersGroup;
        return $this;
    }

    /**
     * @return UsersGroupEntity
     */
    protected function getUsersGroup(): UsersGroupEntity
    {
        if (!$this->usersGroup) throw new InvalidArgumentException("setUsersGroup() first");
        return $this->usersGroup;
    }




    /**
     * @return Generator
     */
    public function clearCalendar()
    {
        $query = $this->entityManager
            ->createQueryBuilder()
            ->delete(CalendarEntity::class, 'e')
            ->where('e.usersGroups = ?1')->setParameter(1, $this->getUsersGroup());

        $query->getQuery()->execute();
        return $this;
    }


    /**
     * @return CalendarEntity[]
     * @throws \Exception
     */
    public function generateByCampaigns(bool $compressedResult = false)
    {
        $calendarList = new CalendarList();

        foreach ($this->campaigns as $campaign) {

            $time = $campaign->getRealizedFrom();
            $time->setTime($time->format('H'), 0, 0);

            /*
             * create table $devicesMetrics
             * [device][day][hour] = true
             */
            $devicesMetrics = [];
            $deviceGroupsMetrics = [];

            foreach ($campaign->getTargetGroups() as $targetGroupEntity) {
                foreach ($targetGroupEntity->getDevicesMetrics() as $deviceMetricEntity) {
                    if ($deviceMetricEntity->hasDevice()) {
                        $devicesMetrics[$deviceMetricEntity->getDevice()->getId()][$deviceMetricEntity->getBlockDay()][$deviceMetricEntity->getBlockHour()] = true;

                    } elseif ($deviceMetricEntity->hasDeviceGroup()) {
                        $deviceGroupsMetrics[$deviceMetricEntity->getDeviceGroup()->getId()][$deviceMetricEntity->getBlockDay()][$deviceMetricEntity->getBlockHour()] = true;
                    }
                }
            }

            while ($time <= $campaign->getRealizedTo()) {

                $hour      = Time::getHour($time);
                $dayOfWeek = Time::getDayOfWeek($time);

                foreach ($campaign->getDevicesGroups() as $devicesGroups) {
                    $isTime = isset($deviceGroupsMetrics[$devicesGroups->getId()])
                        ? isset($deviceGroupsMetrics[$devicesGroups->getId()][$dayOfWeek][$hour])
                        : true;

                    $toTime = clone $time;
                    $toTime->modify('+1 hour');

                    $percentage = 0;

                    try {
                        if ($isTime) {
                            if (!$record = $calendarList->getRecord($campaign, DateTime::from($time), DateTime::from($toTime))) {
                                $record = $this->createRecord($campaign, $this->getUsersGroup(), DateTime::from($time), DateTime::from($toTime), $percentage);
                            }

                            $record->addDeviceGroup($devicesGroups);
                            $calendarList->addRecord($record);
                        }

                    } catch (OutOfRangeException $e) {

                    }


                }

                foreach ($campaign->getDevices() as $device) {
                    $isTime = isset($devicesMetrics[$device->getId()])
                        ? isset($devicesMetrics[$device->getId()][$dayOfWeek][$hour])
                        : true;

                    $toTime = clone $time;
                    $toTime->modify('+1 hour');

                    $percentage = 0;

                    try {
                        if ($isTime) {
                            if (!$record = $calendarList->getRecord($campaign, DateTime::from($time), DateTime::from($toTime))) {
                                $record = $this->createRecord($campaign, $this->getUsersGroup(), DateTime::from($time), DateTime::from($toTime), $percentage);
                            }

                            $record->addDevice($device);
                            $calendarList->addRecord($record);
                        }

                    } catch (OutOfRangeException $e) {

                    }


                }

                $time->modify('+1 hour');
            }
        }

        $result = [];
        if ($calendarList->hasCalendar()) {
            $result = $compressedResult
                ? $calendarList->getCompressedCalendar()
                : $calendarList->getCalendar();

            $this->onGenerated($result);

            $this->entityManager->persist($result)->flush();
        }

        return $result;
    }


    /**
     * @return CalendarEntity[]
     * @throws \Exception
     */
    public function generateByUsersGroup(bool $compressedResult = false)
    {
        /** @var CampaignEntity[] $campaigns */
        $this->campaigns = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->addSelect('tg')
            ->addSelect('d')
            ->addSelect('dg')
            ->from(CampaignEntity::class, 'e')
            ->join('e.usersGroups', 'ug')
            ->leftJoin('e.devices', 'd')
            ->leftJoin('e.devicesGroups', 'dg')
            ->leftJoin('e.targetGroups', 'tg')
            ->where('ug =?1')->setParameter(1, $this->getUsersGroup())
            ->andWhere('e.active = true')
            ->getQuery()
            ->getResult();

        return $this->generateByCampaigns($compressedResult);
    }


    /**
     * create record to calendar
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
     */
    public function createRecord(CampaignEntity $campaignEntity, UsersGroupEntity $usersGroupEntity, DateTime $from, DateTime $to = null, int $percentage = 0, $force = false)
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