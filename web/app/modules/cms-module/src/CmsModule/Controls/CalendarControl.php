<?php


namespace CmsModule\Controls;

use CmsModule\Entities\CalendarEntity;
use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\UsersGroupEntity;
use CmsModule\Facades\CalendarFacade;
use CmsModule\Forms\IBaseForm;
use CmsModule\OutOfRangeException;
use CmsModule\Presenters\BasePresenter;
use CmsModule\Repositories\CalendarRepository;
use CmsModule\Repositories\CampaignRepository;
use Flame\Application\UI\Control;
use Nette\Utils\DateTime;
use Tracy\Debugger;
use Tracy\ILogger;

interface ICalendarControlFactory
{
    /** @return CalendarControl */
    function create();
}

/**
 * Class CalendarControl
 * @package CmsModule\Controls
 */
class CalendarControl extends Control
{

    /** @var CalendarFacade @inject */
    public $calendarFacade;

    /** @var CalendarRepository @inject */
    public $calendarRepository;

    /** @var IBaseForm @inject */
    public $baseForm;

    /** @var CampaignRepository @inject */
    public $campaignRepository;

    /** @var UsersGroupEntity */
    private $usersGroupEntity;

    /** @var bool  */
    private $modifyRealizationCampaignIfNeed = true;


    /**
     * render modal
     */
    public function render()
    {
        $template = $this->getTemplate();
        $template->renderType = 'modal';
        $template->render();
    }

    /**
     * render normal
     */
    public function renderNormal()
    {
        $template = $this->getTemplate();
        $template->renderType = 'normal';
        $template->campaigns = $this->campaignRepository->findBy(['usersGroups' => $this->usersGroupEntity]);
        $template->render();
    }


    /**
     * @param $start
     * @param $end
     * @throws \Nette\Application\AbortException
     *
     * return json  [['title' => 'Event name', 'start' => '2019-08-01 08:00', 'end' => '2019-08-01 10:00']]
     */
    public function handleGetEvents($start, $end)
    {
        $start = $start ? $start : $this->getPresenter()->getParameter('start');
        $end   = $end ? $end : $this->getPresenter()->getParameter('end');

        $query = $this->calendarRepository
            ->getQuery()
            ->betweenFromTo($start, $end)
            ->withDevices()
            ->withDevicesGroups()
            ->withCampaigns()
            ->orderByFromTo();

        /** @var CalendarEntity[] $records */
        $records = $this->calendarRepository->fetch($query);

        $result = [];
        foreach ($records as $record) {

            $devices = '';
            foreach ($record->getDevices() as $device) {
                $devices .= "{$device->getName()}, ";
            }
            $devices = rtrim($devices, ", ");

            $deviceGroups = '';
            foreach ($record->getDevicesGroups() as $device) {
                $deviceGroups .= "{$device->getName()}, ";
            }
            $deviceGroups = rtrim($deviceGroups, ", ");

            $result[] =
            [
                'id' => $record->getId(),
                'title' => $record->getCampaign()->getName(),
                'start' => $record->getFrom()->format('Y-m-d H:i'),
                'end' => $record->getTo()->format('Y-m-d H:i'),
                'classNames' => [$record->getCampaign()->getTag() ? $record->getCampaign()->getTag() : 'tagNo'],
                'devices' => $devices,
                'deviceGroups' => $deviceGroups,
            ];
        }

        $this->getPresenter()->sendResponse(new \Nette\Application\Responses\JsonResponse($result));
    }

    /**
     * add campaign event
     *
     * @param CampaignEntity $id
     * @param string $time
     * @throws \Nette\Application\AbortException
     */
    public function handleNewEvent($id, $time)
    {
        /** @var BasePresenter $presenter */
        $presenter = $this->getPresenter();

        if (!$id || !$time) {
            if ($this->presenter->isAjax()) {
                $presenter->payload->move_event = false;
                $presenter->sendPayload();
            }

            return;
        }

        /** @var CampaignEntity $campaignEntity */
        if ($campaignEntity = $this->campaignRepository->find($id)) {

            try {
                $translator = $presenter->translateMessage();
                $title      = $translator->translate('campaignPage.management');

                $calendarEntity = $this->createCalendarEntity($campaignEntity, $time);
                $this->calendarRepository->getEntityManager()->persist($calendarEntity)->flush();

                $message    = $translator->translate('campaignPage.calendar.new', null, [
                    'name' => $campaignEntity->getName(),
                    'from' => $calendarEntity->getFrom()->format("j. n. Y H:i"),
                ]);
                $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_CAMPAIGN_EDIT_SUCCESS);

                $presenter->payload->calendar_refresh = true;
                $presenter->sendPayload();


            } catch (OutOfRangeException $e) {
                $presenter->flashMessage($e->getMessage(), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);

            } catch (\Exception $e) {
                Debugger::log($e, ILogger::WARNING);
            }
        }

        $presenter->ajaxRedirect('this', null, 'flash');
    }


    /**
     * move campaign event
     *
     * @param $id
     * @param $time
     * @throws \Nette\Application\AbortException
     */
    public function handleMoveEvent($id, $time)
    {
        /** @var BasePresenter $presenter */
        $presenter = $this->getPresenter();

        if (!$id || !$time) {
            if ($this->presenter->isAjax()) {
                $presenter->payload->move_event = false;
                $presenter->sendPayload();
            }

            return;
        }

        /** @var CalendarEntity $entity */
        if ($entity = $this->calendarRepository->find($id)) {
            try {

                /*
                 * calc length
                 */
                $lengthInSecs = $entity->getTo()->getTimestamp() - $entity->getFrom()->getTimestamp();

                $entity->setFrom($time)
                       ->setTo(DateTime::from($time)->modify("+ $lengthInSecs seconds"));

                $this->calendarRepository->getEntityManager()->persist($entity)->flush();

                $translator = $presenter->translateMessage();
                $title      = $translator->translate('campaignPage.management');
                $message    = $translator->translate('campaignPage.calendar.moved', null, [
                    'name' => $entity->getCampaign()->getName(),
                    'from' => $entity->getFrom()->format("j. n. Y H:i"),
                ]);
                $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_CAMPAIGN_EDIT_SUCCESS);

            } catch (\Exception $e) {
                Debugger::log($e, ILogger::WARNING);
            }
        }

        $presenter->ajaxRedirect('this', null, 'flash');
    }


    /**
     * resize campaign event
     *
     * @param $id
     * @param $time
     * @throws \Nette\Application\AbortException
     */
    public function handleResizeEvent($id, $time)
    {
        /** @var BasePresenter $presenter */
        $presenter = $this->getPresenter();

        if (!$id || !$time) {
            if ($this->presenter->isAjax()) {
                $presenter->payload->resize_event = false;
                $presenter->sendPayload();
            }

            return;
        }

        /** @var CalendarEntity $entity */
        if ($entity = $this->calendarRepository->find($id)) {
            try {
                $entity->setTo($time);
                $this->calendarRepository->getEntityManager()->persist($entity)->flush();

                $translator = $presenter->translateMessage();
                $title      = $translator->translate('campaignPage.management');
                $message    = $translator->translate('campaignPage.calendar.resized', null, [
                    'name' => $entity->getCampaign()->getName(),
                    'from' => $entity->getFrom()->format("j. n. Y H:i"),
                    'to' => $entity->getTo()->format("j. n. Y H:i"),
                ]);
                $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_CAMPAIGN_EDIT_SUCCESS);

            } catch (\Exception $e) {
                Debugger::log($e, ILogger::WARNING);
            }
        }

        $presenter->ajaxRedirect('this', null, 'flash');
    }


    /**
     * not use
     *
     * @return \CmsModule\Forms\BaseForm
     */
    protected function createComponentNewEvent()
    {
        $form = $this->baseForm->create();
        $form->addFormClass(['ajax']);

        /** @var CampaignEntity[] $campaignEntities */
        $campaignEntities = $this->campaignRepository->findBy([]);

        $campaigns = [];
        foreach ($campaignEntities as $campaignEntity) {
            $campaigns[$campaignEntity->getId()] = $campaignEntity->getName();
        }

        $form->addHidden('datetime');
        $form->addSelect('campaign', 'Kampaň', $campaigns);
        $form->addSubmit('send', 'Uložit');

        $form->bootstrap3Render();
        $form->onSuccess[] = function ($form, $values) {

            /** @var CampaignEntity $campaignEntity */
            if ($campaignEntity = $this->campaignRepository->find($values->campaign)) {
                $calendarEntity = $this->createCalendarEntity($campaignEntity, $values->datetime);
                $this->calendarRepository->getEntityManager()->persist($calendarEntity)->flush();
            }

            /** @var BasePresenter $presenter */
            $presenter = $this->getPresenter();

            if ($presenter->isAjax()) {
                $presenter->payload->calendar_refresh = true;
                $presenter->payload->modal_hide = '#newEventModal';
                $presenter->sendPayload();
            }
        };

        return $form;
    }


    /**
     * @param CampaignEntity $campaignEntity
     * @param $dt
     * @throws \Exception
     * @throws \OutOfRangeException
     *
     * @return CalendarEntity
     */
    private function createCalendarEntity(CampaignEntity $campaignEntity, $dt)
    {
        $datetime = new DateTime($dt);

        if ($datetime->format('H:i') == '00:00') {
            $datetime->setTime(7, 0);
        }

        return $this->calendarFacade->getRecord($campaignEntity, $this->usersGroupEntity, $datetime, null, 0, $this->modifyRealizationCampaignIfNeed);
    }


    protected function createComponentEditEvent()
    {
        $form = $this->baseForm->create();
        $form->setAutoButtonClass(false);
        $form->addFormClass(['ajax']);

        $form->addHidden('id');

        $params = $form->addGroup('Parametry');

        $form->addText('name', 'Kampaň')
            ->setDisabled(true);

        $form->addText('from', 'Čas od')
            ->setDisabled(true);

        $form->addText('to', 'Čas do')
            ->setDisabled(true);

        $devices = $form->addGroup('Obrazovky');
        $form->addTextArea('devices', 'Zařízení', null, 3)
            ->setDisabled(true);

        $form->addTextArea('deviceGroups', 'Skupiny zařízení', null, 3)
            ->setDisabled(true);


        $form->addSubmit('send', 'Smazat')
            ->getControlPrototype()->setAttribute('class', 'btn btn-danger btn-md');

        $form->bootstrap3Render();
        $form->onSuccess[] = function ($form, $values) {

            /** @var CalendarEntity $calendarEntity */
            if ($calendarEntity = $this->calendarRepository->find($values->id)) {
                $this->calendarRepository->getEntityManager()->remove($calendarEntity)->flush();
            }

            /** @var BasePresenter $presenter */
            $presenter = $this->getPresenter();

            if ($presenter->isAjax()) {
                $presenter->payload->calendar_refresh = true;
                $presenter->payload->modal_hide = '#editEventModal';
                $presenter->sendPayload();
            }
        };

        return $form;
    }


    /**
     * @param UsersGroupEntity $usersGroupEntity
     * @return CalendarControl
     */
    public function setUsersGroupEntity(UsersGroupEntity $usersGroupEntity): CalendarControl
    {
        $this->usersGroupEntity = $usersGroupEntity;
        return $this;
    }




}