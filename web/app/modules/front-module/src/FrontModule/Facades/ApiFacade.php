<?php


namespace FrontModule\Facades;


use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\MediumEntity;
use CmsModule\Facades\Calendar\PlayList;
use CmsModule\Facades\DeviceFacade;
use CmsModule\Facades\DeviceLogFacade;
use CmsModule\Repositories\CalendarRepository;
use CmsModule\Repositories\CampaignRepository;
use CmsModule\Repositories\DeviceRepository;
use CmsModule\Repositories\MediaRepository;
use Nette\DateTime;
use Nette\Http\IRequest;
use Nette\Utils\Validators;
use Tracy\Debugger;
use Ublaboo\ImageStorage\ImageStorage;

class ApiFacade
{

    /** @var CalendarRepository */
    private $calendarRepository;

    /** @var CampaignRepository */
    private $campaignRepository;

    /** @var DeviceRepository */
    private $deviceRepository;

    /** @var MediaRepository */
    private $mediaRepository;

    /** @var DeviceLogFacade */
    private $deviceLogFacade;

    /** @var DeviceFacade */
    private $deviceFacade;

    /** @var ImageStorage */
    private $imageStorage;

    /** @var IRequest */
    private $url;


    /**
     * ApiFacade constructor.
     * @param CalendarRepository $calendarRepository
     * @param CampaignRepository $campaignRepository
     * @param DeviceRepository $deviceRepository
     * @param MediaRepository $mediaRepository
     * @param DeviceLogFacade $deviceLogFacade
     * @param DeviceFacade $deviceFacade
     * @param ImageStorage $imageStorage
     * @param IRequest $url
     */
    public function __construct(CalendarRepository $calendarRepository, CampaignRepository $campaignRepository, DeviceRepository $deviceRepository,
                                MediaRepository $mediaRepository, DeviceLogFacade $deviceLogFacade, DeviceFacade $deviceFacade,
                                ImageStorage $imageStorage, IRequest $url)
    {
        $this->calendarRepository = $calendarRepository;
        $this->campaignRepository = $campaignRepository;
        $this->deviceRepository   = $deviceRepository;
        $this->mediaRepository    = $mediaRepository;
        $this->deviceLogFacade    = $deviceLogFacade;
        $this->deviceFacade       = $deviceFacade;
        $this->imageStorage       = $imageStorage;
        $this->url                = $url;
    }


    /**
     * @param $did
     * @param null $ssid
     * @param null $p
     * @param null $realizedFrom
     * @param null $realizedTo
     * @param bool $activeDevice
     * @param bool $activeCampaigns
     * @return array
     * @throws \Ublaboo\ImageStorage\ImageResizeException
     * @throws \Exception
     */
    public function createPlayList($did, $ssid = null, $p = null, $realizedFrom = null, $realizedTo = null, $activeDevice = true, $activeCampaigns = true)
    {
        $validParams = true;
        $result      = ["result" => false, "reason" => "unexpected params"];

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
        if ($validParams && $realizedTo && $realizedFrom && ($realizedFrom > $realizedTo) ) {
            $result      = ["result" => false, "reason" => "realizedFrom param is bigger then realizedTo"];
            $validParams = false;
        }

        if ($validParams) {

            /** @var DeviceEntity $device */
            if ($device = $this->deviceRepository->findOneBy(['sn' => $did])) {

                if (($p == null) || ($p && $device->getPsw() == $device->getHashDevicePassword($p))) {

                    $query = $this->calendarRepository
                        ->getQuery()
                        ->byDeviceGroupSn($did)
                        ->byDeviceSn($did)
//                        ->deviceActive($activeDevice)
                        ->campaignActive($activeCampaigns)
//                        ->inCampaignTimeRange()
                        ->withCampaigns()
                        ->orderByFromTo()
                        ->orderByCampaign()
                    ;

                    if (!$realizedFrom) {
                        $realizedFrom = date('Y-m-d H:i');
                    }

                    if ($realizedTo) {
                        if ($realizedFrom > $realizedTo) $realizedTo = $realizedFrom;
                    }

                    if ($realizedFrom && !$realizedTo) {
                        $query->realizedFrom($realizedFrom);

                    } elseif ($realizedTo && !$realizedFrom) {
                        $query->realizedTo($realizedTo);

                    } elseif ($realizedFrom && $realizedTo) {
                        $query->betweenFromTo($realizedFrom, $realizedTo);
                    }

                    $calendars = $this->calendarRepository->fetch($query)->getIterator()->getArrayCopy();

                    $playList  = (new PlayList($calendars))
                        ->setFrom($realizedFrom)
                        ->setTo($realizedTo);

                    if ($mediaList = $playList->createMediumList()) {
                        $result = [];

                        foreach ($mediaList as $item) {

                            $campaign = $item->getMediumDataEntity()->getCampaign();
                            $medium = $item->getMediumDataEntity();

                            $out = [
                                'id'   => $medium->getId(),
                                'from' => $item->getFrom(),
                                'fromString' => $item->getFrom()->format('Y-m-d H:i'),
                                'to'   => $item->getTo(),
                                'toString' => $item->getTo()->format('Y-m-d H:i'),
                                //                                'mediaKeywords'     => implode(' ', $this->getMediaDataKeywords($campaign)),

                                //                    'name'              => $campaign->getName(),
                                //                    'realizedFrom'      => $campaign->getRealizedFrom()->format('Y-m-d H:i'),
                                //                    'realizedTo'        => $campaign->getRealizedTo()->format('Y-m-d H:i'),
                                //                    'realizedFromHuman' => $campaign->getRealizedFrom()->format('j. n. Y H:i'),
                                //                    'realizedToHuman'   => $campaign->getRealizedTo()->format('j. n. Y H:i'),
                                //                    'active'            => $campaign->isActive(),
                                //                    'keywords'          => $campaign->getKeywords(),
                                //                    'version'           => $campaign->getVersion(),
                            ];

                            if ($item->getMediumDataEntity()->getMedium()->getType() == MediumEntity::TYPE_IMAGE) {

                                $absoluteUrl = $this->url->getUrl()->baseUrl . $medium->getFilePath();
                                $image       = $this->imageStorage->fromIdentifier([$medium->getIdentifier(), '190x150']);

                                $previewPath        = $image->data_dir . DIRECTORY_SEPARATOR . $image->identifier;
                                $absolutePreviewUrl = $this->url->getUrl()->baseUrl . $previewPath;

                                if (!file_exists($medium->getFilePath())) {
                                    $absoluteUrl        = false;
                                    $absolutePreviewUrl = false;
                                }

                                $out += [
                                    'type'        => 'image',
                                    'mimeType'    => $medium->getType(),
                                    'length'      => $item->getLength(),
                                    'path'        => $absoluteUrl,
                                    'previewPath' => $absolutePreviewUrl,
                                ];
                            }

                            $result[] = $out;
                        }

                    } else {
                        $result = ["result" => false, "reason" => "playlist is empty"];
                    }

                    $this->deviceFacade->setOnline($device);

                } else {
                    $result = ["result" => false, "reason" => "device password `$p` incorrect"];
                }

            } else {
                $result = ["result" => false, "reason" => "device `$did` not found"];
            }

            $this->createLog($ssid, $device, $result);
        }

        return $result;
    }


    /*
     * --------------------------------------------------------------------------------------------------------------
     * response methods
     * --------------------------------------------------------------------------------------------------------------
     */



    /*
     * --------------------------------------------------------------------------------------------------------------
     * @internal methods
     * --------------------------------------------------------------------------------------------------------------
     */


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


    /**
     * @param $active
     * @return bool
     */
    private function checkValidActive($active)
    {
        if (Validators::isInRange($active, [0, 1]) || in_array($active, ['ignore', 'all'])) return true;
        else return false;
    }

    /**
     * @param $value
     * @param $min
     * @param $max
     * @return bool
     */
    private function checkValidDouble($value, $min, $max)
    {
        return is_numeric($value) && is_float($value) && ($value >= $min) && ($value <= $max);
    }

    /**
     * @param array $params
     */
    private function filterParams(array & $params)
    {
        unset($params['id'], $params['action'], $params['locale'], $params['do'], $params['p'], $params['dev']);

        foreach ($params as $key => $param) {
            if ($param === null) {
                unset($params[$key]);
            }
        }
    }


    protected function createLog($ssid, $device, &$result)
    {
        if ($ssid && $device) {
            $params = $_GET;
            $this->filterParams($params);

            $command = $this->getCommand(__FUNCTION__);
            $this->logCommand($ssid, $command, $device, $params, $result);
        }
    }


    /**
     * @param $methodName
     * @return string
     */
    private function getCommand($methodName)
    {
        $command = str_replace('create', '', $methodName);
        return lcfirst($command);
    }

    /**
     * @param $ssid
     * @param $command
     * @param DeviceEntity $deviceEntity
     * @param array $params
     * @param $result
     */
    private function logCommand($ssid, $command, DeviceEntity $deviceEntity, array $params, $result)
    {
        $this->deviceLogFacade->createLog($ssid, $command, $deviceEntity, $params, $result);
    }


}