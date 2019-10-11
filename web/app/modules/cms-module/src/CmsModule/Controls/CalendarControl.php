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
use Nette\Forms\Form;
use Tracy\Debugger;

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

    public function handleGetEvents($start, $end)
    {
        $start = $start ? $start : $this->getPresenter()->getParameter('start');
        $end = $end ? $end : $this->getPresenter()->getParameter('end');


        /** @var CalendarEntity[] $records */
        $records = $this->calendarRepository->findBy(['datetime >=' => $start, 'datetime <=' => $end], null);

//        Debugger::barDump($records);

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

//        Debugger::barDump($result);



//        $result = [['title' => 'Ahoj', 'start' => '2019-08-01']];

        $this->getPresenter()->sendResponse(new \Nette\Application\Responses\JsonResponse($result));
    }

    public function handleMoveEvent($id, $time)
    {
        /** @var CalendarEntity $entity */
        if ($entity = $this->calendarRepository->find($id)) {
            $entity->setDatetime($time);
            $this->calendarRepository->getEntityManager()->persist($entity)->flush();
        }

        if ($this->presenter->isAjax()) {
//            $this->
        }
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



//        $form->bindEntity(new CampaignEntity());
        $form->bootstrap3Render();

        $form->onSuccess[] = function ($form, $values) {

//            dump($values);

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
//                $this->redrawControl('calendarFormModal');
//                $this->redrawControl('calendarFormScript');
            }

//            $presenter->ajaxRedirect( 'this');

//            die();;
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