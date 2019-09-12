<?php


namespace CmsModule\Presenters;


use CmsModule\Controls\FlashMessageControl;
use CmsModule\Entities\MetricEntity;
use CmsModule\Entities\MetricStatisticEntity;
use CmsModule\Entities\ShopEntity;
use CmsModule\Entities\TargetGroupEntity;
use CmsModule\Entities\UsersGroupEntity;
use CmsModule\Facades\ReachFacade;
use CmsModule\Forms\BaseForm;
use Devrun\CmsModule\Controls\DataGrid;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Nette\Application\UI\Multiplier;
use Nette\Forms\Container;
use Nette\Utils\DateTime;
use Nette\Utils\Validators;
use Tracy\Debugger;

class ReachPresenter extends BasePresenter
{

    /** @var ReachFacade @inject */
    public $reachFacade;

    /** @var integer @persistent */
    public $editTargetGroup;

    /** @var integer @persistent */
    public $editTargetParamGroup;

    /** @var integer @persistent */
    public $editShop;

    /** @var MetricEntity[] */
    private $metrics;


    public function renderDefault()
    {

    }


    /**
     * shop add signal
     */
    public function handleAddShop()
    {
        $this->editShop = null;
//        $this->payload->url = $this->link('this');
        $this->ajaxRedirect('this', null, ['editShopFormModal']);
    }

    /**
     * shop edit signal
     * @param $id
     */
    public function handleEditShop($id)
    {
        /** @var TargetGroupEntity $entity */
        if (!$entity = $this->reachFacade->getShopRepository()->find($id)) {

            $title   = $this->translateMessage()->translate('devicePage.management');
            $message = $this->translateMessage()->translate('devicePage.device_not_found', null, ['id' => $id]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
            $this->ajaxRedirect('this', null, ['flash']);
            return;
        }

        $this->editShop = $id;
        $this->ajaxRedirect('this', null, ['editShopFormModal']);
    }


    public function handleAddTargetGroup()
    {

        $this->editTargetGroup = null;
        $this->payload->url = $this->link('this');
        $this->ajaxRedirect('this', null, ['editTargetGroupFormModal']);
    }


    public function handleEditTargetGroup($id)
    {
        /** @var TargetGroupEntity $entity */
        if (!$entity = $this->reachFacade->getTargetGroupRepository()->find($id)) {

            $title   = $this->translateMessage()->translate('devicePage.management');
            $message = $this->translateMessage()->translate('devicePage.device_not_found', null, ['id' => $id]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
            $this->ajaxRedirect('this', null, ['flash']);
            return;
        }

        $this->editTargetGroup = $id;
        $this->ajaxRedirect('this', null, ['editTargetGroupFormModal']);
    }


    public function handleDeleteTargetGroup($id)
    {
        /** @var TargetGroupEntity $entity */
        if (!$entity = $this->reachFacade->getTargetGroupRepository()->find($id)) {

            $title   = $this->translateMessage()->translate('devicePage.management');
            $message = $this->translateMessage()->translate('devicePage.device_group_not_found', null, ['id' => $id]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
            $this->ajaxRedirect('this', null, ['flash']);
            return;
        }

        $this->reachFacade->getEntityManager()->remove($entity)->flush();

        $title   = $this->translateMessage()->translate('devicePage.management');
        $message = $this->translateMessage()->translate('devicePage.device_group_removed', null, ['name' => $entity->getName()]);
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);


        $this->editTargetGroup = null;
        $this->payload->url = $this->link('this');
        $this->ajaxRedirect('this', ['reachGridControl'], ['flash']);
    }


    public function handleDeleteShop($id)
    {
        /** @var TargetGroupEntity $entity */
        if (!$entity = $this->reachFacade->getShopRepository()->find($id)) {

            $title   = $this->translateMessage()->translate('reachPage.shop.management');
            $message = $this->translateMessage()->translate('reachPage.shop.not_found', null, ['id' => $id]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
            $this->ajaxRedirect('this', null, ['flash']);
            return;
        }

        $this->reachFacade->getEntityManager()->remove($entity)->flush();

        $title   = $this->translateMessage()->translate('reachPage.shop.management');
        $message = $this->translateMessage()->translate('reachPage.shop.removed', null, ['name' => $entity->getName()]);
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);

        $this->editShop = null;
        $this->payload->url = $this->link('this');
        $this->ajaxRedirect('this', ['shopGridControl'], ['flash']);
    }


    public function handleEditTargetGroupParams()
    {
        $this->ajaxRedirect('this', null, 'editTargetGroupParamsFormModal');
    }


    public function handleEditMetricParams()
    {
        $this->ajaxRedirect('this', null, 'editMetricParamFormModal');
    }




    /**
     * @return DataGrid
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    protected function createComponentTargetGroupGridControl()
    {
        $grid = new DataGrid();
        $grid->setTranslator($this->translator);

        $query = $this->reachFacade->getTargetGroupRepository()->createQueryBuilder('e');


        $grid->setDataSource($query);

        $grid->addColumnText('id', 'ID')
            ->setTranslatableHeader(false)
            ->setSortable()
            ->setFitContent();

        $grid->addColumnText('name', 'messages.reachPage.targetGroup.name')
            ->setSortable()
            ->setFilterText();


        $grid->addAction('edit', 'messages.reachPage.targetGroup.update', 'editTargetGroup!')
            ->setIcon('pencil')
            ->setDataAttribute('target', '#targetGroupFormModal')
            ->setDataAttribute('title', $this->translateMessage()->translate('reachPage.targetGroup.edit'))
            ->setDataAttribute('toggle', 'ajax-modal')
            ->setTitle('messages.reachPage.targetGroup.update')
            ->setClass('btn btn-xs btn-info');


        $grid->addAction('delete', 'messages.reachPage.targetGroup.delete', 'deleteTargetGroup!')
            ->setIcon('trash')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat cílovou skupinu `{$item->name}`?";
            });


        $grid->addToolbarButton('addTargetGroup!', 'messages.reachPage.targetGroup.add')
            ->addAttributes([
                'data-target' => '#targetGroupFormModal',
                'data-toggle' => 'ajax-modal',
                'data-title' => $this->translateMessage()->translate('reachPage.targetGroup.add'),
            ])
            ->setClass('btn btn-xs btn-success')
            ->setIcon('fa fa-plus');



        return $grid;
    }


    /**
     * @return DataGrid
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    protected function createComponentShopGridControl()
    {
        $grid = new DataGrid();
        $grid->setTranslator($this->translator);

        $query = $this->reachFacade->getShopRepository()->createQueryBuilder('e');


        $grid->setDataSource($query);

        $grid->addColumnText('id', 'ID')
            ->setTranslatableHeader(false)
            ->setSortable()
            ->setFitContent();

        $grid->addColumnText('name', 'messages.reachPage.shop.name')
            ->setSortable()
            ->setFilterText();


        $grid->addAction('edit', 'messages.reachPage.shop.update', 'editShop!')
            ->setIcon('pencil')
            ->setDataAttribute('target', '#shopFormModal')
            ->setDataAttribute('toggle', 'ajax-modal')
            ->setDataAttribute('title', $this->translateMessage()->translate('reachPage.shop.edit'))
            ->setTitle('messages.reachPage.shop.edit')
            ->setClass('btn btn-xs btn-info');


        $grid->addAction('delete', 'messages.reachPage.shop.delete', 'deleteShop!')
            ->setIcon('trash')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat prodejnu `{$item->name}`?";
            });


        $grid->addToolbarButton('addShop!', 'messages.reachPage.shop.add')
            ->addAttributes([
                'data-target' => '#shopFormModal',
                'data-toggle' => 'ajax-modal',
                'data-title' => $this->translateMessage()->translate('reachPage.shop.add'),
            ])
            ->setClass('btn btn-xs btn-success')
            ->setIcon('fa fa-plus');


        return $grid;
    }



    protected function createComponentStatisticsGridControl()
    {
        $metricEntities = $this->getMetrics();

        return new Multiplier(function ($index) use ($metricEntities) {

            $grid = new DataGrid();
            $grid->setTranslator($this->translator);
            $grid->setPagination(false);

            /** @var MetricEntity $metricEntity */
            $metricEntity = $metricEntities[$index];

            $data = [
                ['id' => 1, 7 => null, 8 => null, 9 => null, 10 => null, 11 => null, 12 => null, 13 => null, 14 => null, 15 => null, 16 => null, 17 => null, 18 => null, 19 => null, 20 => null, 21 => null, 22 => null],
                ['id' => 2, 7 => null, 8 => null, 9 => null, 10 => null, 11 => null, 12 => null, 13 => null, 14 => null, 15 => null, 16 => null, 17 => null, 18 => null, 19 => null, 20 => null, 21 => null, 22 => null],
                ['id' => 3, 7 => null, 8 => null, 9 => null, 10 => null, 11 => null, 12 => null, 13 => null, 14 => null, 15 => null, 16 => null, 17 => null, 18 => null, 19 => null, 20 => null, 21 => null, 22 => null],
                ['id' => 4, 7 => null, 8 => null, 9 => null, 10 => null, 11 => null, 12 => null, 13 => null, 14 => null, 15 => null, 16 => null, 17 => null, 18 => null, 19 => null, 20 => null, 21 => null, 22 => null],
                ['id' => 5, 7 => null, 8 => null, 9 => null, 10 => null, 11 => null, 12 => null, 13 => null, 14 => null, 15 => null, 16 => null, 17 => null, 18 => null, 19 => null, 20 => null, 21 => null, 22 => null],
                ['id' => 6, 7 => null, 8 => null, 9 => null, 10 => null, 11 => null, 12 => null, 13 => null, 14 => null, 15 => null, 16 => null, 17 => null, 18 => null, 19 => null, 20 => null, 21 => null, 22 => null],
                ['id' => 7, 7 => null, 8 => null, 9 => null, 10 => null, 11 => null, 12 => null, 13 => null, 14 => null, 15 => null, 16 => null, 17 => null, 18 => null, 19 => null, 20 => null, 21 => null, 22 => null],
            ];

            foreach ($metricEntity->getMetricStatistics() as $metricStatistic) {
                $data[$metricStatistic->getBlockDay() - 1][$metricStatistic->getBlockTime()->format('G')] = $metricStatistic->getValue();
            }

            $grid->setDataSource($data);

            $grid->addColumnText('id', 'den')
                ->setTranslatableHeader(false)
                ->setSortable()
//                ->setFitContent()
                ->setReplacement([1 => 'pondělí', 2 => 'úterý', 3 => 'středa', 4 => 'čtvrtek', 5 => 'pátek', 6 => 'sobota', 7 => 'neděle' ]);

            foreach (range(7, 22) as $item) {
                $grid->addColumnNumber($item, "$item:00")
                    ->setTranslatableHeader(false)
                    ->setFormat(0, '.', '')
                    ->setSortable()
                    ->setFitContent()
                    ->setEditableInputType('number', ['class' => 'form-control'])
//                    ->setEditableValueCallback(function ($row) use ($item) {
//                        return $row[$item];
//                    })
                    ->setEditableCallback(function ($id, $value) use ($item, $metricEntity, $grid) {

                        if (Validators::is($value, 'numeric:-32768..32767|string:0')) {
                            /** @var MetricStatisticEntity $entity */
                            if (!$entity = $this->reachFacade->getMetricStatisticRepository()->findOneBy(['metric' => $metricEntity, 'blockDay' => $id, 'blockTime' => DateTime::createFromFormat('G', $item)])) {
                                $entity = new MetricStatisticEntity($metricEntity);
                                $entity->setBlockDay($id);
                            }
                            $entity->setBlockTime($item);
                            $entity->setValue($value);

                            $this->reachFacade->getMetricStatisticRepository()->getEntityManager()->persist($entity)->flush();
                            $grid->validResponse();
                        }

                        $message = "input not valid";
                        $grid->invalidResponse($message);
                    });
            }

            return $grid;
        });
    }



    protected function createComponentTargetGroupForm($name)
    {
        $form = $this->reachFacade->getTargetGroupFormFactory()->create();

        /** @var TargetGroupEntity $entity */
        if (!$this->editTargetGroup || !$entity = $this->reachFacade->getTargetGroupRepository()->find($this->editTargetGroup)) {
            $entity = new TargetGroupEntity("Nová skupina");
            $entity->setUsersGroup($this->userEntity->getGroup());
        }


        $form->create();
        $form->bindEntity($entity);
        $form->bootstrap3Render();
        $form->onSuccess[] = function (BaseForm $form, $values) {

//            $form->isSubmitted()
            $byName = $form->isSubmitted()->getName();



            /** @var TargetGroupEntity $entity */
//            $entity = $form->getEntity();


//            Debugger::barDump($entity);
            Debugger::barDump($byName);
            Debugger::barDump($values);
//            die(__METHOD__);



        };

        return $form;
    }


    protected function createComponentTargetGroupParamForm()
    {
        $form = $this->reachFacade->getTargetGroupParamFormFactory()->create();
        $form->create();

        $form->bindEntity($entity = $this->userEntity->getGroup());
        $form->bootstrap3Render();

        $form->onSuccess[] = function (BaseForm $form, $values) {

            if ($byName = $form->isSubmitted()->getName()) {
                if ($byName == 'send') {
                    $message = "Uživatelský přístup  přidán!";
                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa uživatelských přístupů', FlashMessageControl::TOAST_SUCCESS);

                    $this->ajaxRedirect('this', null, ['editMetricParamFormModal', 'flash']);

                } elseif ( $byName == 'addParam') {
                    $message = "Parametr přidán";
//                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa uživatelských přístupů', FlashMessageControl::TOAST_SUCCESS);
                    $this->ajaxRedirect('this', null, ['editTargetGroupParamsFormModal', 'flash']);

                } elseif ( $byName == 'removeParam') {
                    $this->ajaxRedirect('this', null, ['editTargetGroupParamsFormModal', 'flash']);

                } elseif ( $byName == 'addValue') {
                    $message = "Hodnota přidána";
//                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa uživatelských přístupů', FlashMessageControl::TOAST_SUCCESS);
                    $this->ajaxRedirect('this', null, ['editTargetGroupParamsFormModal', 'flash']);

                } elseif ( $byName == 'removeValue') {
                    $message = "Hodnota odebrána";
//                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa uživatelských přístupů', FlashMessageControl::TOAST_SUCCESS);
                    //                $this->ajaxRedirect('this', null, ['editMetricParamFormModal', 'flash']);
                    $this->ajaxRedirect('this', null, ['editTargetGroupParamsFormModal', 'flash']);
                }
            }




            /** @var TargetGroupEntity $entity */
            $entity = $form->getEntity();


            Debugger::barDump($byName);
//            Debugger::barDump($entity);
//            Debugger::barDump($values);
//            die(__METHOD__);

        };



        return $form;
    }


    protected function createComponentMetricParamForm()
    {
        $form = $this->reachFacade->getMetricParamFormFactory()->create();

        $form->create();
        $form->bootstrap3Render();
        $form->bindEntity($entity = $this->userEntity->getGroup());


        $form->onSuccess[] = function (BaseForm $form, $values) {

            /** @var UsersGroupEntity $entity */
            $entity = $form->getEntity();

            if ($byName = $form->isSubmitted()->getName()) {
                if ($byName == 'send') {
                    $message = "Uživatelský přístup  přidán!";
                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa uživatelských přístupů', FlashMessageControl::TOAST_SUCCESS);

    //            $this['reachGridControl']->reload();
                    $this->ajaxRedirect('this', null, ['editMetricParamFormModal', 'flash']);

                } elseif ( $byName == 'add') {
                    $message = "Metrika přidána";
                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa uživatelských přístupů', FlashMessageControl::TOAST_SUCCESS);
                    $this->ajaxRedirect('this', null, ['editMetricParamFormModal', 'flash']);

                } elseif ( $byName == 'remove') {
                    $message = "Metrika odebrána";
                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa uživatelských přístupů', FlashMessageControl::TOAST_SUCCESS);
    //                $this->ajaxRedirect('this', null, ['editMetricParamFormModal', 'flash']);
                    $this->ajaxRedirect('this', null, ['editMetricParamFormModal', 'flash']);
                }
            }
        };

        return $form;
    }


    /**
     * shop form factory
     *
     * @return \CmsModule\Forms\ShopForm
     */
    protected function createComponentShopForm($name)
    {
        $form = $this->reachFacade->getShopFormFactory()->create();
        $form->setTranslator($this->translator->domain("messages.forms.$name"));

        $entity = null;
        if ($this->editShop) {
            $entity = $this->reachFacade->getShopRepository()->find($this->editShop);
        }

        if (!$entity) {
            $entity = new ShopEntity();
            $entity->setUsersGroup($this->userEntity->getGroup());
        }

        $form->setEntity($entity);

        $form
            ->create()
            ->bootstrap3Render()
            ->bindEntity($entity)
            ->onSuccess[] = function (BaseForm $form, $values) {

            $title   = $this->translateMessage()->translate('reachPage.shop.management');
            $message = $this->translateMessage()->translate('reachPage.shop.updated', null, ['name' => $form->getEntity()->name]);

            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
            $this->ajaxRedirect('this', ['shopGridControl'], ['editShopFormModal', 'flash']);
        };

        return $form;
    }


    /**
     * reach data form factory
     *
     * @return \CmsModule\Forms\ReachForm
     */
    protected function createComponentReachForm($name)
    {
        $form = $this->reachFacade->getReachFormFactory()->create();
        $form->setTranslator($this->translator->domain("messages.forms.$name"));

        $entity = new MetricEntity();
        $entity->setUsersGroup($this->userEntity->getGroup());

        $form
//            ->setShops($shops = $this->reachFacade->getShopRepository()->findPairs([], 'name'))
//            ->setTargetGroups($targetGroups = $this->reachFacade->getTargetGroupRepository()->findPairs([], 'name'))
//            ->setMetricParams($metricParams = $this->reachFacade->getMetricParamRepository()->findPairs([], 'name'))
            ->setUserGroup($this->userEntity->getGroup())
            ->create()
            ->bootstrap3Render()
            ->bindEntity($entity);

        $form->onError[] = function (BaseForm $form) {
            $this->ajaxRedirect('this', null, ['reachFormModal', 'editReachFormModal']);
            $this->payload->success = false;
        };

        $form->onSuccess[] = function (BaseForm $form, $values) {

            $title   = $this->translateMessage()->translate('reachPage.targetGroup.management');
            $message = $this->translateMessage()->translate('reachPage.targetGroup.updated', null, ['name' => $form->getEntity()->id]);

            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
            $this->ajaxRedirect('this', null, ['statistics', 'reachFormModal', 'editReachFormModal', 'flash']);
            $this->payload->success = true;
        };

        return $form;
    }






    /**
     * -----------------------------------------------------------------------------------------
     * GETTERS SETTERS
     * -----------------------------------------------------------------------------------------
     */


    /**
     * @return MetricEntity[]
     */
    public function getMetrics(): array
    {
        if (null === $this->metrics) {
            $this->metrics = $this->reachFacade->getMetricRepository()->getUserGroupMetrics($this->userEntity->getGroup());
        }

        return $this->metrics;
    }

}