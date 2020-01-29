<?php

namespace Devrun\CmsModule\Controls;

use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Entities\DeviceMetricEntity;
use CmsModule\Entities\TargetGroupEntity;
use CmsModule\Entities\UsersGroupEntity;
use CmsModule\Facades\DeviceFacade;
use CmsModule\InvalidArgumentException;
use CmsModule\Presenters\BasePresenter;
use CmsModule\Repositories\TargetGroupRepository;
use Flame\Application\UI\Control;
use Nette\Utils\DateTime;

interface IDeviceTargetGroupsControlFactory
{
    /** @return DeviceTargetGroupsControl */
    function create();
}

/**
 * Class DeviceTargetGroupsControl
 * @package Devrun\CmsModule\Controls
 * @method onClose($id, DeviceTargetGroupsControl $control)
 */
class DeviceTargetGroupsControl extends Control
{

    /**
     * @var array
     */
    private $data;

    /** @var DeviceFacade @inject */
    public $deviceFacade;

    /** @var TargetGroupRepository @inject */
    public $targetGroupRepository;


    /** @var TargetGroupEntity[] */
    private $targetGroupEntities;

    /** @var DeviceEntity */
    private $deviceEdit;

    /** @var DeviceGroupEntity */
    private $deviceGroupEdit;

    /** @var UsersGroupEntity */
    private $usersGroup;


    public $onClose = [];

    public function render()
    {
        $template = $this->getTemplate();
        $template->hours = $this->getDeviceData();
        $template->paramDay = $this->getParameterId('day');
        $template->paramTime = $this->getParameterId('time');
        $template->paramValues = $this->getParameterId('values');
        $template->targetGroups = $this->getTargetGroups();

        $template->render();
    }


    public function renderDeviceGroup()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'DeviceGroupTargetGroupsControl.latte');
        $template->hours = $this->getDeviceGroupData();
        $template->paramDay = $this->getParameterId('day');
        $template->paramTime = $this->getParameterId('time');
        $template->paramValues = $this->getParameterId('values');
        $template->targetGroups = $this->getTargetGroups();

        $template->render();
    }


    public function renderDevice()
    {
        $this->render();
    }


    public function handleDevicesChange($day, $time, $values)
    {
//        dump($day);
//        dump($time);

        if (is_string($values)) {
            $values = json_decode($values);
        }
        if (!$values) $values = [];

//        dump($values);



        if (!$entity = $this->deviceFacade
            ->getDeviceMetricRepository()
            ->findOneBy(['device' => $this->getDeviceEdit(), 'blockDay' => $day, 'blockTime' => $blockTime = DateTime::from($time)])) {

            $entity = new DeviceMetricEntity($day, $blockTime, $this->getDeviceEdit());
        }

        $targetGroupsChanged = [];
        foreach ($values as $value) {
            $targetGroupsChanged[] = $this->getTargetGroups()[$value];
        }

//        dump($targetGroupsChanged);

        $tgs = $this->targetGroupRepository->findBy(['id' => $values]);

        $em = $this->deviceFacade->getEntityManager();
        if ($tgs) {
            $entity->setTargetGroups($tgs);
            $em->persist($entity);

        } else {
            $em->remove($entity);
        }
        $em->flush();
        $this->flashMessage("Uživatel {$entity->getBlockDay()} upravena", 'success');
        //                        $this['usersGridControl']->redrawItem($id);


        /** @var BasePresenter $presenter */
        $presenter = $this->getPresenter();

        $presenter->ajaxRedirect('this', null, 'flash');
    }


    public function handleDeviceGroupChange($day, $time, $values)
    {
//        dump($day);
//        dump($time);
//        dump($values);


        if (is_string($values)) {
            $values = json_decode($values);
        }
        if (!$values) $values = [];

        if (!$entity = $this->deviceFacade
            ->getDeviceMetricRepository()
            ->findOneBy(['deviceGroup' => $this->getDeviceGroupEdit(), 'blockDay' => $day, 'blockTime' => $blockTime = DateTime::from($time)])) {

            $entity = new DeviceMetricEntity($day, $blockTime, null, $this->getDeviceGroupEdit());
        }

        $targetGroupsChanged = [];
        foreach ($values as $value) {
            $targetGroupsChanged[] = $this->getTargetGroups()[$value];
        }

//        dump($targetGroupsChanged);

        $tgs = $this->targetGroupRepository->findBy(['id' => $values]);

        $em = $this->deviceFacade->getEntityManager();
        if ($tgs) {
            $entity->setTargetGroups($tgs);
            $em->persist($entity);

        } else {
            $em->remove($entity);
        }
        $em->flush();
        $this->flashMessage("Uživatel {$entity->getBlockDay()} upravena", 'success');
        //                        $this['usersGridControl']->redrawItem($id);


        /** @var BasePresenter $presenter */
        $presenter = $this->getPresenter();

//        $this->redrawControl('deviceGroupsTargetGroups');
        $presenter->ajaxRedirect('this', null, ['flash', 'deviceGroupTargetGroups']);
//        $presenter->ajaxRedirect('this', null, ['flash']);

    }


    public function handleClose($id)
    {
        $this->onClose($id, $this);
    }


    /**
     * @return mixed
     */
    protected function getDeviceData()
    {
        $data = [];

        for ($i = 0;$i <= 23; $i++) {
            $data[] = [
                'id' => sprintf("%02s:00", $i),
                1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []
            ];
        }

        /** @var DeviceMetricEntity[] $deviceMetrics */
        $deviceMetrics = $this->deviceFacade->getDeviceMetricRepository()->getDeviceMetrics($this->getDeviceEdit());

        foreach ($deviceMetrics as $deviceMetric) {
            $targetGroups = $deviceMetric->getTargetGroups();
            $ids = [];
            foreach ($targetGroups as $targetGroup) {
                $ids[$targetGroup->getId()] = $targetGroup->getName();
            }

            $data[$deviceMetric->getBlockTime()->format('G')][$deviceMetric->getBlockDay()] = $ids;
        }

        return $data;
    }


    /**
     * @return mixed
     */
    protected function getDeviceGroupData()
    {
        $data = [];

        for ($i = 0;$i <= 23; $i++) {
            $data[] = [
                'id' => sprintf("%02s:00", $i),
                1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []
            ];
        }

        /** @var DeviceMetricEntity[] $deviceMetrics */
        $deviceMetrics = $this->deviceFacade->getDeviceMetricRepository()->getDeviceGroupMetrics($this->getDeviceGroupEdit());

        foreach ($deviceMetrics as $deviceMetric) {
            $targetGroups = $deviceMetric->getTargetGroups();
            $ids = [];
            foreach ($targetGroups as $targetGroup) {
                $ids[$targetGroup->getId()] = $targetGroup->getName();
            }

            $data[$deviceMetric->getBlockTime()->format('G')][$deviceMetric->getBlockDay()] = $ids;
        }

        return $data;
    }



    /**
     * @param DeviceEntity $deviceEdit
     * @return DeviceTargetGroupsControl
     */
    public function setDeviceEdit(DeviceEntity $deviceEdit): DeviceTargetGroupsControl
    {
        $this->deviceEdit = $deviceEdit;
        return $this;
    }

    /**
     * @param DeviceGroupEntity $deviceGroupEdit
     * @return DeviceTargetGroupsControl
     */
    public function setDeviceGroupEdit(DeviceGroupEntity $deviceGroupEdit): DeviceTargetGroupsControl
    {
        $this->deviceGroupEdit = $deviceGroupEdit;
        return $this;
    }

    /**
     * @return DeviceEntity
     */
    public function getDeviceEdit(): DeviceEntity
    {
        if (!$this->deviceEdit) {
            throw new InvalidArgumentException('setDeviceEdit first');
        }
        return $this->deviceEdit;
    }

    /**
     * @return DeviceGroupEntity
     */
    public function getDeviceGroupEdit(): DeviceGroupEntity
    {
        if (!$this->deviceGroupEdit) {
            throw new InvalidArgumentException('setDeviceGroupEdit first');
        }
        return $this->deviceGroupEdit;
    }





    /**
     * @return array [1 => 'starší muži', 2 => 'matky s dětmi']
     */
    protected function getTargetGroups()
    {
        if (null === $this->targetGroupEntities) {
            $this->targetGroupEntities = $this->targetGroupRepository->findPairs(['usersGroup' => $this->getUsersGroup()], 'name');
        }

        return $this->targetGroupEntities;
    }

    /**
     * @return UsersGroupEntity
     */
    private function getUsersGroup(): UsersGroupEntity
    {
        if (!$this->usersGroup) {
            throw new InvalidArgumentException('set usersGroup first');
        }
        return $this->usersGroup;
    }

    /**
     * @param UsersGroupEntity $usersGroup
     * @return DeviceTargetGroupsControl
     */
    public function setUsersGroup(UsersGroupEntity $usersGroup): DeviceTargetGroupsControl
    {
        $this->usersGroup = $usersGroup;
        return $this;
    }




}