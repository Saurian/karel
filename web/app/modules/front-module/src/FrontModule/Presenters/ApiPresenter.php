<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    ApiPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace FrontModule\Presenters;

use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\MediumDataEntity;
use CmsModule\Facades\DeviceLogFacade;
use CmsModule\Repositories\CalendarRepository;
use CmsModule\Repositories\CampaignRepository;
use CmsModule\Repositories\DeviceRepository;
use CmsModule\Repositories\MediaRepository;
use FrontModule\Facades\ApiFacade;
use FrontModule\Forms\IPlayListFormFactory;
use Nette\DateTime;
use Nette\Http\IRequest;
use Nette\Utils\Validators;
use Tracy\Debugger;
use Ublaboo\ImageStorage\ImageStorage;

class ApiPresenter extends BasePresenter
{

    /** @var CalendarRepository @inject */
    public $calendarRepository;

    /** @var CampaignRepository @inject */
    public $campaignRepository;

    /** @var DeviceRepository @inject */
    public $deviceRepository;

    /** @var MediaRepository @inject */
    public $mediaRepository;

    /** @var DeviceLogFacade @inject */
    public $deviceLogFacade;

    /** @var ImageStorage @inject */
    public $imageStorage;

    /** @var IPlayListFormFactory @inject */
    public $playListFormFactory;


    /** @var IRequest @inject */
    public $url;

    /** @var ApiFacade @inject */
    public $apiFacade;



    private function logCommand($ssid, $command, DeviceEntity $deviceEntity, array $params, $result)
    {
        $this->deviceLogFacade->createLog($ssid, $command, $deviceEntity, $params, $result);
    }


    public function renderDefault($did, $p, $ssid = null)
    {
        if ($did && $p) {
            /** @var DeviceEntity $device */
            if ($device = $this->deviceRepository->findDevice($did)) {
                $result = $device->getPsw() == $device->getHashDevicePassword($p)
                    ? ["result" => true]
                    : ["result" => false, "reason" => "password is not valid"];

            } else {
                $result = ["result" => false, "reason" => "device $did not found"];
            }

            if ($this->isAjax()) {
                $encode = json_encode($result);
                $this->template->json = $encode;
                $this->redrawControl('result');
            }

            $params = $this->params;
            $this->filterParams($params);

            $command = 'deviceCheck';
            if ($ssid && $device) $this->logCommand($ssid, $command, $device, $params, $result);

            $this->sendJson($result);

        }

        $result = ["result" => false, "reason" => "unexpected request"];
        $this->sendJson($result);
    }


    private function testMatch()
    {
        //        $url = "mailto:pixman.cz_gv24p21h3647j0kmaira5s4irc@group.calendar.google.com";
//        echo "<a href='$url'>fefe</a>";
//        die();
        $mode = 'boolean';
        $slovo = 'test word slovenština';
//        $slovo = "word";

        $result = $this->campaignRepository->createQueryBuilder('e')
            ->addSelect('md')
//            ->select( 'partial e.{ id, name, keywords }')
//            ->addSelect('md')
            ->addSelect("(MATCH (e.name, e.keywords) AGAINST (:against $mode) + MATCH (md.keywords) AGAINST (:against $mode))  AS HIDDEN mySelectAlias")
//            ->addSelect("(MATCH (md.keywords) AGAINST (:against $mode) + MATCH (e.name, e.keywords) AGAINST (:against $mode) )  AS HIDDEN mySelectAlias")
//            ->addSelect('5 * (MATCH (e.name, e.keywords) AGAINST (:against boolean) ) AS HIDDEN mySelectAlias')
//            ->addSelect('5 * (MATCH (e.name, e.keywords) AGAINST (:against boolean) ) AS HIDDEN mySelectAlias')
//            ->addSelect('5 * (MATCH (md.keywords) AGAINST (:against boolean) ) AS HIDDEN mySelectAlias2')
            ->leftJoin('e.mediaData', 'md')
//            ->select('e.id, e.keywords')
//            ->where("MATCH (e.keywords) AGAINST (:against boolean) > 0")
//            ->where("MATCH (e.keywords, :against) > 0")
            ->where("(MATCH (e.name, e.keywords) AGAINST (:against $mode) + MATCH (md.keywords) AGAINST (:against $mode) ) > 0.0")
//            ->where("(MATCH (md.keywords) AGAINST (:against $mode) + MATCH (e.name, e.keywords) AGAINST (:against $mode) ) > 0.0")
//            ->addOrderBy('mySelectAlias2', 'desc')
            ->addOrderBy('mySelectAlias', 'desc')
//            ->addOrderBy("5 * MATCH (e.name, e.keywords) AGAINST (:against boolean) + MATCH (md.keywords) AGAINST (:against boolean) ")


//            ORDER BY 5 * MATCH (`name`) AGAINST ('AUTO') + MATCH (`description`) AGAINST ('AUTO') DESC
            ->setParameter('against', $slovo)

//            ->setMaxResults(10)
            ->getQuery()
            ->getResult();



        dump($result);
        dump(array_reverse($result));

        die("END");
    }


    /**
     * @param $did
     * @param null $ssid
     * @param null $p
     * @param null $realizedFrom
     * @param null $realizedTo
     * @param null $activeDevice
     * @param null $activeCampaigns
     * @throws \Nette\Application\AbortException
     * @throws \Exception
     */
    public function handleGimmePlayList($did, $ssid = null, $p = null, $realizedFrom = null, $realizedTo = null, $activeDevice = true, $activeCampaigns = true)
    {
        $result = $this->apiFacade->createPlayList($did, $ssid, $p, $realizedFrom, $realizedTo, $activeDevice, $activeCampaigns);
        $this->sendJson($result);
    }


    public function handleGimmeBestMatchedResultForCampaigns($did, $kw, $ssid = null, $byWord = false, $match = 0.0, $p = null, $realizedFrom = null, $realizedTo = null, $activeDevice = null, $activeCampaigns = null)
    {
        $validParams = true;
        $result      = ["result" => false, "reason" => "unexpected params"];

        if (!$this->checkValidDouble($match, 0.0, 1.0)) {
            $result      = ["result" => false, "reason" => "match param is not valid [expect 0.0 ... 1.0]"];
            $validParams = false;
        }
        if ($validParams && $activeDevice && !$this->checkValidActive($activeDevice)) {
            $result      = ["result" => false, "reason" => "activeDevice param is not valid [expect 0,1,ignore]"];
            $validParams = false;
        }
        if ($validParams && $activeCampaigns && !$this->checkValidActive($activeCampaigns)) {
            $result      = ["result" => false, "reason" => "activeCampaigns param is not valid [expect 0,1,ignore]"];
            $validParams = false;
        }
        if ($validParams && $realizedFrom && !$this->checkValidDateTime($realizedFrom)) {
            $result      = ["result" => false, "reason" => "realizedFrom param is not valid [expect yyyy-mm-dd, yyyy-mm-dd hh:ii]"];
            $validParams = false;
        }
        if ($validParams && $realizedTo && !$this->checkValidDateTime($realizedTo)) {
            $result      = ["result" => false, "reason" => "realizedTo param is not valid [expect yyyy-mm-dd, yyyy-mm-dd hh:ii]"];
            $validParams = false;
        }

        if ($validParams) {

            /** @var DeviceEntity $device */
            if ($device = $this->deviceRepository->findOneBy(['sn' => $did])) {

                if (($p == null) || ($p && $device->getPsw() == $device->getHashDevicePassword($p))) {

                    $mode = $byWord ? 'boolean' : null;
                    $query = $this->campaignRepository->getQuery()
                        ->withMediaData()
                        ->matchByKeywords($kw, $match, $mode);
//                        ->byDevice($device);

                    if ($realizedFrom) {
                        $query->realizedFrom($realizedFrom);
                    }
                    if ($realizedTo) {
                        $query->realizedTo($realizedTo);
                    }
                    if ($activeDevice != 'ignore') {
                        if ($activeDevice === null) $activeDevice = 1;
                        if (Validators::isInRange($activeDevice, [0, 1])) {
//                            $query->deviceActive($activeDevice);
                        }
                    }
                    if ($activeCampaigns != 'ignore') {
                        if ($activeCampaigns === null) $activeCampaigns = 1;
                        if (Validators::isInRange($activeCampaigns, [0, 1])) {
//                            if ($activeCampaigns) $query->isActive();
//                            else $query->isNotActive();
                        }
                    }

                    $campaigns = $this->campaignRepository->fetch($query->orderByPosition());

                    if (count($campaigns) > 0) {
                        $result    = $this->createCampaignsMatchResult($campaigns);

                    } else {
                        $result = ["result" => false, "reason" => "campaigns for device `$did` with keywords `$kw` is empty"];
                    }

                } else {
                    $result = ["result" => false, "reason" => "device password `$p` incorrect"];
                }

            } else {
                $result = ["result" => false, "reason" => "device `$did` not found"];
            }

            $params = $this->params;
            $this->filterParams($params);

            $command = $this->getCommand(__FUNCTION__);
            if ($ssid && $device) $this->logCommand($ssid, $command, $device, $params, $result);
        }

//        dump($result);
        $this->sendJson($result);
    }



    public function handleGimmeAllActiveCampaignsData($did, $ssid = null, $p = null, $realizedFrom = null, $realizedTo = null, $activeDevice = null, $activeCampaigns = null)
    {
        $validParams = true;
        $result      = ["result" => false, "reason" => "unexpected params"];

        if ($activeDevice && !$this->checkValidActive($activeDevice)) {
            $result      = ["result" => false, "reason" => "activeDevice param is not valid [expect 0,1,ignore]"];
            $validParams = false;
        }
        if ($validParams && $activeCampaigns && !$this->checkValidActive($activeCampaigns)) {
            $result      = ["result" => false, "reason" => "activeCampaigns param is not valid [expect 0,1,ignore]"];
            $validParams = false;
        }
        if ($validParams && $realizedFrom && !$this->checkValidDateTime($realizedFrom)) {
            $result      = ["result" => false, "reason" => "realizedFrom param is not valid [expect yyyy-mm-dd, yyyy-mm-dd hh:ii]"];
            $validParams = false;
        }
        if ($validParams && $realizedTo && !$this->checkValidDateTime($realizedTo)) {
            $result      = ["result" => false, "reason" => "realizedTo param is not valid [expect yyyy-mm-dd, yyyy-mm-dd hh:ii]"];
            $validParams = false;
        }

        if ($validParams) {

            /** @var DeviceEntity $device */
            if ($device = $this->deviceRepository->findOneBy(['sn' => $did])) {

                if (($p == null) || ($p && $device->getPsw() == $device->getHashDevicePassword($p))) {
                    $query = $this->mediaRepository->getMediaQuery()
                        ->inDevice($device);

                    if ($realizedFrom) {
                        $query->realizedFrom($realizedFrom);
                    }
                    if ($realizedTo) {
                        $query->realizedTo($realizedTo);
                    }

                    if ($activeDevice != 'ignore') {
                        if ($activeDevice === null) $activeDevice = 1;
                        if (Validators::isInRange($activeDevice, [0, 1])) {
                            $query->activeDevice($activeDevice);
                        }
                    }
                    if ($activeCampaigns != 'ignore') {
                        if ($activeCampaigns === null) $activeCampaigns = 1;
                        if (Validators::isInRange($activeCampaigns, [0, 1])) {
                            $query->activeCampaigns($activeCampaigns);
                        }
                    }

                    /** @var MediumDataEntity[] $media */
                    $media  = $this->mediaRepository->fetch($query);
                    if (count($media) > 0) {
                        $result = $this->createDeviceResult($media);

                    } else {
                        $result = ["result" => false, "reason" => "data for device `$did` is empty"];
                    }

                } else {
                    $result = ["result" => false, "reason" => "device password `$p` incorrect"];
                }

            } else {
                $result = ["result" => false, "reason" => "device `$did` not found"];
            }

            $params = $this->params;
            $this->filterParams($params);

            $command = $this->getCommand(__FUNCTION__);
            if ($ssid && $device) $this->logCommand($ssid, $command, $device, $params, $result);
        }

        $this->sendJson($result);
    }


    /**
     * get json campaigns by filters
     *
     * @param      $did
     * @param null $realizedFrom
     * @param null $realizedTo
     * @param null $activeDevice
     * @param null $activeCampaigns
     */
    public function handleGimmeAllActiveCampaigns($did, $ssid = null, $p = null, $realizedFrom = null, $realizedTo = null, $activeDevice = null, $activeCampaigns = null)
    {
        $validParams = true;
        $result      = ["result" => false, "reason" => "unexpected params"];

        if ($activeDevice && !$this->checkValidActive($activeDevice)) {
            $result      = ["result" => false, "reason" => "activeDevice param is not valid [expect 0,1,ignore]"];
            $validParams = false;
        }
        if ($validParams && $activeCampaigns && !$this->checkValidActive($activeCampaigns)) {
            $result      = ["result" => false, "reason" => "activeCampaigns param is not valid [expect 0,1,ignore]"];
            $validParams = false;
        }
        if ($validParams && $realizedFrom && !$this->checkValidDateTime($realizedFrom)) {
            $result      = ["result" => false, "reason" => "realizedFrom param is not valid [expect yyyy-mm-dd, yyyy-mm-dd hh:ii]"];
            $validParams = false;
        }
        if ($validParams && $realizedTo && !$this->checkValidDateTime($realizedTo)) {
            $result      = ["result" => false, "reason" => "realizedTo param is not valid [expect yyyy-mm-dd, yyyy-mm-dd hh:ii]"];
            $validParams = false;
        }

        if ($validParams) {
            /** @var DeviceEntity $device */
            if ($device = $this->deviceRepository->findOneBy(['sn' => $did])) {

                if (($p == null) || ($p && $device->getPsw() == $device->getHashDevicePassword($p))) {
                    $query = $this->campaignRepository->getQuery()
                        ->byDevice($device);

                    if ($realizedFrom) {
                        $query->realizedFrom($realizedFrom);
                    }
                    if ($realizedTo) {
                        $query->realizedTo($realizedTo);
                    }
                    if ($activeDevice != 'ignore') {
                        if ($activeDevice === null) $activeDevice = 1;
                        if (Validators::isInRange($activeDevice, [0, 1])) {
                            $query->deviceActive($activeDevice);
                        }
                    }
                    if ($activeCampaigns != 'ignore') {
                        if ($activeCampaigns === null) $activeCampaigns = 1;
                        if (Validators::isInRange($activeCampaigns, [0, 1])) {
                            if ($activeCampaigns) $query->isActive();
                            else $query->isNotActive();
                        }
                    }

//                    dump($query->getLastQuery());
                    $campaigns = $this->campaignRepository->fetch($query->orderByPosition());

                    if (count($campaigns) > 0) {
                        $result    = $this->createCampaignsResult($campaigns);

                    } else {
                        $result = ["result" => false, "reason" => "campaigns for device `$did` is empty"];
                    }

                } else {
                    $result = ["result" => false, "reason" => "device password `$p` incorrect"];
                }

            } else {
                $result = ["result" => false, "reason" => "device $did not found"];
            }

            $params = $this->params;
            $this->filterParams($params);

            $command = $this->getCommand(__FUNCTION__);
            if ($ssid && $device) $this->logCommand($ssid, $command, $device, $params, $result);
        }

        $this->sendJson($result);
    }


    /**
     * @param $datetime
     *
     * @return bool
     */
    private function checkValidDateTime($datetime)
    {
        if (DateTime::createFromFormat('Y-m-d', $datetime)) return true;
        if (DateTime::createFromFormat('Y-m-d H:i', $datetime)) return true;
        return false;
    }

    private function checkValidActive($active)
    {
        if (Validators::isInRange($active, [0, 1]) || in_array($active, ['ignore', 'all'])) return true;
        else return false;
    }

    private function checkValidDouble($value, $min, $max)
    {
        return is_numeric($value) && is_float($value) && ($value >= $min) && ($value <= $max);
    }


    /**
     * @param MediumDataEntity[] $media
     *
     * @return array
     */
    private function createDeviceResult($media)
    {
        $result = [];
        foreach ($media as $medium) {

            $keywords = $medium->getCampaign()->getKeywords();
            $keywords = str_replace("\n", ' ', $keywords);

            $mediaKeywords = $this->getMediaDataKeywords($medium->getCampaign());

            $out = [
                'id'       => $medium->getId(),
                'campaign' => [
                    'name'              => $medium->getCampaign()->getName(),
                    'realizedFrom'      => $medium->getCampaign()->getRealizedFrom()->format('Y-m-d H:i'),
                    'realizedTo'        => $medium->getCampaign()->getRealizedTo()->format('Y-m-d H:i'),
                    'realizedFromHuman' => $medium->getCampaign()->getRealizedFrom()->format('j. n. Y H:i'),
                    'realizedToHuman'   => $medium->getCampaign()->getRealizedTo()->format('j. n. Y H:i'),
//                    'keywords'          => $medium->getKeywords(),
                    'keywords'          => $medium->getCampaign()->getKeywords(),
                    'mediaKeywords'     => implode(' ', $this->getMediaDataKeywords($medium->getCampaign())),
                    'version'           => $medium->getCampaign()->getVersion(),
                ],

            ];

            $devices = [];
            foreach ($medium->getCampaign()->getDevices() as $device) {
                $devices[] = [
                    'name'    => $device->getName(),
                    'sn'      => $device->getSn(),
                    'version' => $device->getVersion(),
                ];
            }

            if (count($devices) == 1) {
                $devices = $devices[0];
                $out += [
                    'device' => $devices
                ];

            } else {
                $out += [
                    'devices' => $devices
                ];
            }

            if ($medium->getMedium()->getType() == 'image') {
                $absoluteUrl = $this->url->getUrl()->baseUrl . $medium->getFilePath();
                $image       = $this->imageStorage->fromIdentifier([$medium->getIdentifier(), '190x150']);

                $previewPath        = $image->data_dir . DIRECTORY_SEPARATOR . $image->identifier;
                $absolutePreviewUrl = $this->url->getUrl()->baseUrl . $previewPath;

                $timeType = $medium->getTimeType() ? $medium->getTimeType() : "unknown";

                if (!file_exists($medium->getFilePath())) {
                    $absoluteUrl        = false;
                    $absolutePreviewUrl = false;
                }

                $out += [
                    'type'        => 'image',
                    'mimeType'    => $medium->getType(),
                    'time'        => $medium->getTime() . $timeType,
                    'path'        => $absoluteUrl,
                    'previewPath' => $absolutePreviewUrl,
                ];

            } elseif ($medium->getMedium()->getType() == 'url') {
                $timeType = $medium->getTimeType() ? $medium->getTimeType() : "unknown";

                $out += [
                    'type' => $medium->getMedium()->getType(),
                    'url'  => $medium->getUrl(),
                    'time' => $medium->getTime() . $timeType,
                ];

            } elseif ($medium->getMedium()->getType() == 'zip') {
                $absoluteUrl = $this->url->getUrl()->baseUrl . $medium->getFilePath();
                $timeType    = $medium->getTimeType() ? $medium->getTimeType() : "unknown";
                if (!file_exists($medium->getFilePath())) {
                    $absoluteUrl = false;
                }

                $out += [
                    'type' => 'staticWeb',
                    'time' => $medium->getTime() . $timeType,
                    'path' => $absoluteUrl,
                ];

            } elseif ($medium->getMedium()->getType() == 'video') {
                $absoluteUrl = $this->url->getUrl()->baseUrl . $medium->getFilePath();

                if (!file_exists($medium->getFilePath())) {
                    $absoluteUrl = false;
                }

                $out += [
                    'type'     => 'video',
                    'mimeType' => $medium->getType(),
                    'sound'    => $medium->isSound(),
                    'path'     => $absoluteUrl,
                ];
            }

            $result[] = $out;
        }

        return $result;
    }


    /**
     * @param CampaignEntity[] $campaigns
     *
     * @return array
     */
    private function createCampaignsResult($campaigns)
    {
        $result = [];
        foreach ($campaigns as $campaign) {
            $out = [
                'id'                => $campaign->getId(),
                'name'              => $campaign->getName(),
                'realizedFrom'      => $campaign->getRealizedFrom()->format('Y-m-d H:i'),
                'realizedTo'        => $campaign->getRealizedTo()->format('Y-m-d H:i'),
                'realizedFromHuman' => $campaign->getRealizedFrom()->format('j. n. Y H:i'),
                'realizedToHuman'   => $campaign->getRealizedTo()->format('j. n. Y H:i'),
                'active'            => $campaign->isActive(),
                'keywords'          => $campaign->getKeywords(),
//                'mediaKeywords'     => implode(' ', $this->getMediaDataKeywords($campaign)),
                'mediaKeywords'     => $this->getMediaDataKeywords($campaign),
                'version'           => $campaign->getVersion(),
            ];

            $result[] = $out;
        }

        return $result;
    }


    /**
     * @param CampaignEntity[] $campaigns
     *
     * @return array
     */
    private function createCampaignsMatchResult($campaigns)
    {
        $result = [];
        foreach ($campaigns as $campaign) {

            $out = [
                'id'                => $campaign->getId(),
                'name'              => $campaign->getName(),
                'realizedFrom'      => $campaign->getRealizedFrom()->format('Y-m-d H:i'),
                'realizedTo'        => $campaign->getRealizedTo()->format('Y-m-d H:i'),
                'realizedFromHuman' => $campaign->getRealizedFrom()->format('j. n. Y H:i'),
                'realizedToHuman'   => $campaign->getRealizedTo()->format('j. n. Y H:i'),
                'active'            => $campaign->isActive(),
                'keywords'          => $campaign->getKeywords(),
                'mediaKeywords'     => implode(' ', $this->getMediaDataKeywords($campaign)),
                'version'           => $campaign->getVersion(),
            ];

            $result[] = $out;
        }

        return $result;
    }


    private function getMediaDataKeywords(CampaignEntity $campaign)
    {
        $dataKeywords = [];
        foreach ($campaign->getMediaData() as $mediumDataEntity) {
            if ($mediumDataEntity->getKeywords()) {
                if ($mediumDataEntity->getKeywords() == 'barva') $mediumDataEntity->setKeywords("barva for");

//                $dataKeywords = array_merge($dataKeywords, explode(' ', $this->filterKeywords($mediumDataEntity->getKeywords())));
                $dataKeywords = array_merge($dataKeywords, explode("\n", ($mediumDataEntity->getKeywords())));

            }
        }

        return array_unique($dataKeywords);
    }


    private function filterKeywords($keywords)
    {
        $keywords = str_replace("\n", " ", $keywords);
        return $keywords;
    }


    private function filterParams(array & $params)
    {
        unset($params['id'], $params['action'], $params['locale'], $params['do'], $params['p'], $params['dev']);

        foreach ($params as $key => $param) {
            if ($param === null) {
                unset($params[$key]);
            }
        }
    }


    private function getCommand($methodName)
    {
        $command = str_replace('handle', '', $methodName);
        return lcfirst($command);
    }


    /**
     * @return \FrontModule\Forms\PlayListForm
     */
    protected function createComponentPlayListForm()
    {
        $form = $this->playListFormFactory->create();

        $devices = $this->deviceRepository->findPairs([], 'name', [], 'sn');

        $form->setDevices($devices)
             ->createForm()
             ->bootstrap3Render();

        $form->onSuccess[] = function ($form, $values) {

            $this->redirect('gimmePlayList!', (array) $values);
//            $result = $this->handleGimmePlayList($values->did, $values->ssid, $values->p, $values->realizedFrom, $values->realizedTo, $values->activeDevice, $values->activeCampaigns);
            $result = $this->apiFacade->createPlayList($values->did, $values->ssid, $values->p, $values->realizedFrom, $values->realizedTo, $values->activeDevice, $values->activeCampaigns);

            $result = json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

            Debugger::$maxLength = 20000;
            $this->template->result = Debugger::dump($result, true);
        };

        return $form;
    }

}