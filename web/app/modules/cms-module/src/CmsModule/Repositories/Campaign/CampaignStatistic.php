<?php


namespace CmsModule\Repositories\Campaign;

use CmsModule\Entities\CampaignEntity;
use DateTime;
use Devrun\Utils\Time;

/**
 * Class CampaignStatistic
 * @package CmsModule\Repositories\Campaign
 */
class CampaignStatistic
{

    private $_statistics = [];


    /**
     * Statistic constructor.
     * @param CampaignEntity $campaignEntity
     */
    public function __construct(CampaignEntity $campaignEntity)
    {
        $this->setStatistics($campaignEntity);
    }

    /*
     * private setters
     * ______________________________________________________________________________________________________________
     */


    /**
     * @param CampaignEntity $campaignEntity
     * @return CampaignStatistic
     */
    private function setStatistics(CampaignEntity $campaignEntity): CampaignStatistic
    {
        $_statistics = [];

        foreach ($campaignEntity->getMetrics() as $metricEntity) {
            $statistics = $metricEntity->getMetricStatistics();
            foreach ($statistics as $statistic) {
                $_statistics[$metricEntity->getId()][$statistic->getBlockDay()][$statistic->getBlockHour()] = $statistic->getValue();
            }
        }

        $this->_statistics = $_statistics;
        return $this;
    }


    /*
     * public getters
     * ______________________________________________________________________________________________________________
     */


    /**
     * @param DateTime $dateTime
     * @return bool
     */
    public function isTimeInMetricsTimeRange(DateTime $dateTime)
    {
        if (empty($this->_statistics)) {
            return true;
        }

        $hour      = Time::getHour($dateTime);
        $dayOfWeek = Time::getDayOfWeek($dateTime);

        $isTimeInMetricStatisticsSet = false;
        foreach ($this->_statistics as $metricStatistics) {
            if (isset($metricStatistics[$dayOfWeek][$hour])) {
                $isTimeInMetricStatisticsSet = true;
                break;
            }
        }

        return $isTimeInMetricStatisticsSet;
    }


    /**
     * @param DateTime $dateTime
     * @return int
     */
    public function getPercentageUse(DateTime $dateTime)
    {
        if (empty($this->_statistics) || !$this->isTimeInMetricsTimeRange($dateTime)) {
            return 100;
        }

        $hour      = Time::getHour($dateTime);
        $dayOfWeek = Time::getDayOfWeek($dateTime);
        $percentage = 0;

        foreach ($this->_statistics as $metricStatistics) {
            $maxValue = $this->getMaximumStatisticValueOfMetric($metricStatistics);

            if (isset($metricStatistics[$dayOfWeek][$hour])) {
                $percentage = max($percentage, ($metricStatistics[$dayOfWeek][$hour] / $maxValue) * 100);
                break;
            }
        }

        return $percentage;
    }



    /**
     * @param $metricStatistics
     * @return bool|int|mixed
     */
    private function getMaximumStatisticValueOfMetric($metricStatistics)
    {
        $result = PHP_INT_MIN;

        foreach ($metricStatistics as $metricStatistic) {
            $result = max($result, max($metricStatistic));
        }

        return $result;
    }


}