<?php


namespace CmsModule\Controls;

use CmsModule\Entities\CalendarEntity;
use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\UsersGroupEntity;
use CmsModule\Forms\IBaseForm;
use CmsModule\Presenters\BasePresenter;
use CmsModule\Repositories\CalendarRepository;
use CmsModule\Repositories\CampaignRepository;
use Flame\Application\UI\Control;
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

    /** @var CalendarRepository @inject */
    public $calendarRepository;

    /** @var IBaseForm @inject */
    public $baseForm;

    /** @var CampaignRepository @inject */
    public $campaignRepository;

    /** @var UsersGroupEntity */
    private $usersGroupEntity;


    public function render()
    {
        $template = $this->getTemplate();
        $template->render();
    }

    /**
     * @param $start
     * @param $end
     * @throws \Nette\Application\AbortException
     *
     * return json  [['title' => 'Event name', 'start' => '2019-08-01']]
     */
    public function handleGetEvents($start, $end)
    {
        $start = $start ? $start : $this->getPresenter()->getParameter('start');
        $end = $end ? $end : $this->getPresenter()->getParameter('end');

        /** @var CalendarEntity[] $records */
        $records = $this->calendarRepository->findBy(['datetime >=' => $start, 'datetime <=' => $end], null);

        $result = [];
        foreach ($records as $record) {
            $result[] =
            [
                'id' => $record->getId(),
                'title' => $record->getCampaign()->getName(),
                'start' => $record->getDatetime()->format('Y-m-d H:i'),
//                'end' => $record->getDatetime()->format('Y-m-d'),
            ];
        }

        $this->getPresenter()->sendResponse(new \Nette\Application\Responses\JsonResponse($result));
    }

    /**
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
                $entity->setDatetime($time);
                $this->calendarRepository->getEntityManager()->persist($entity)->flush();

                $translator = $presenter->translateMessage();
                $title      = $translator->translate('campaignPage.management');
                $message    = $translator->translate('campaignPage.calendar.moved', null, ['name' => $entity->getCampaign()->getName()]);
                $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_CAMPAIGN_EDIT_SUCCESS);

            } catch (\Exception $e) {
                Debugger::log($e, ILogger::WARNING);
            }
        }

        $presenter->ajaxRedirect('this', null, 'flash');
    }


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
                $datetime = new \DateTime($values->datetime);

                if ($datetime->format('H:i') == '00:00') {
                    $datetime->setTime(7, 0);
                }

                $calendarEntity = new CalendarEntity($campaignEntity, $this->usersGroupEntity, $datetime);
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


    protected function createComponentEditEvent()
    {
        $form = $this->baseForm->create();
        $form->setAutoButtonClass(false);
        $form->addFormClass(['ajax']);

        $form->addHidden('id');
        $form->addText('name', 'Kampaň')
            ->setDisabled(true);

        $form->addText('time', 'Čas')
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