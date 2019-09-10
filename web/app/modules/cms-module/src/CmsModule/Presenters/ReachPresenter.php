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
use Nette\Forms\Container;
use Nette\Forms\Form;
use Tracy\Debugger;
use Ublaboo\DataGrid\DataGrid;

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
    protected function createComponentReachGridControl()
    {
        $grid = new DataGrid();
        $grid->setTranslator($this->translator);

        $query = $this->reachFacade->getTargetGroupRepository()->createQueryBuilder('e');


        $grid->setDataSource($query);

        $grid->addColumnText('id', 'ID')
            ->setSortable()
            ->setFitContent();

        $grid->addColumnText('name', 'Název')
            ->setSortable()
            ->setFilterText();


        $grid->addAction('edit', 'Upravit', 'editTargetGroup!')
            ->setIcon('pencil')
            ->setDataAttribute('target', '#targetGroupFormModal')
            ->setDataAttribute('title', $this->translateMessage()->translate('devicePage.editDevice'))
            ->setDataAttribute('toggle', 'ajax-modal')
            ->setTitle($this->translateMessage()->translate('devicePage.editDevice'))
            ->setClass('btn btn-xs btn-info');


        $grid->addAction('delete', 'Smazat', 'deleteTargetGroup!')
            ->setIcon('trash')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat cílovou skupinu `{$item->name}`?";
            });


        $grid->addToolbarButton('addTargetGroup!', 'Přidat cílovou skupinu')
            ->addAttributes([
                'data-target' => '#targetGroupFormModal',
                'data-toggle' => 'ajax-modal',
                'data-title' => $this->translateMessage()->translate('devicePage.edit_device_group'),
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
            ->setSortable()
            ->setFitContent();

        $grid->addColumnText('name', 'Název')
            ->setSortable()
            ->setFilterText();


        $grid->addAction('edit', 'Upravit', 'editShop!')
            ->setIcon('pencil')
            ->setDataAttribute('target', '#shopFormModal')
            ->setDataAttribute('toggle', 'ajax-modal')
            ->setDataAttribute('title', $this->translateMessage()->translate('reachPage.shop.edit'))
            ->setTitle($this->translateMessage()->translate('reachPage.shop.edit'))
            ->setClass('btn btn-xs btn-info');


        $grid->addAction('delete', 'Smazat', 'deleteShop!')
            ->setIcon('trash')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat prodejnu `{$item->name}`?";
            });


        $grid->addToolbarButton('addShop!', 'Přidat prodejnu')
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
        $grid = new DataGrid();
        $grid->setTranslator($this->translator);

        $query = $this->reachFacade->getShopRepository()->createQueryBuilder()
            ->from(MetricEntity::class, 'e')
            ->select('e');

        $metricEntity = $this->reachFacade->getMetricRepository()->find(1);


        $data = [
            ['id' => 1, 'blockDay' => 1, 't700' => 3, 't800' => 8],
            ['id' => 2, 'blockDay' => 2, 't700' => 5, 't800' => 9],

        ];


        $grid->setDataSource($data);

        $grid->addColumnText('id', 'ID')
            ->setSortable()
            ->setFitContent();

        $grid->addColumnText('blockDay', 'den')
            ->setSortable()
            ->setFitContent()
            ->setReplacement([1 => 'pondělí', 2 => 'úterý', 3 => 'středa', 4 => 'čtvrtek', 5 => 'pátek', 6 => 'sobota', 7 => 'neděle' ]);

        $grid->addColumnText('t700', '7:00')
            ->setSortable()
            ->setFitContent();


        $grid->addColumnNumber('t800', '8:00')
            ->setSortable()
            ->setFitContent();

//        $grid->addColumnText('name', 'Název')
//            ->setSortable()
//            ->setFilterText();


        $presenter = $this;

        /*
         * edit
         * __________________________________________________
         */
        $grid->addInlineEdit()->setText('Edit')
            ->onControlAdd[] = function (Container $container) {

            $container->addText('t700')
                ->setAttribute('placeholder', 'user code')
                ->addCondition(Form::FILLED)
                ->addRule(Form::NUMERIC);

//                ->addRule(Form::FILLED)
//                ->addRule(Form::MIN_LENGTH, null, 4);

        };

        $grid->getInlineEdit()->onSetDefaults[] = function (Container $container, $item) {

            $container->setDefaults([
                'id'       => $item['id'],
                't700' => $item['t700'],
            ]);
        };

        /** @var MetricEntity $metricEntity */
        $grid->getInlineEdit()->onSubmit[] = function ($id, $values) use ($presenter, $metricEntity) {

            $em = $this->reachFacade->getEntityManager();

            /** @var MetricStatisticEntity $entity */
            if (!$entity = $em->getRepository(MetricStatisticEntity::class)->find($id)) {
                $entity = new MetricStatisticEntity($metricEntity);

                $entity->blockTime = 10;
                $entity->blockDay = 10;


            }


            try {

                foreach ($values as $key => $value) {
                    $entity->$key = $value;
                }
                Debugger::barDump($values);
                Debugger::barDump($entity);


                $em->persist($entity)->flush();

                $message = "Uživatelský přístup [{$values->username}] upraven";
                $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa uživatelských přístupů', FlashMessageControl::TOAST_INFO);
//                $this['usersGridControl']->redrawItem($id);
//                $this->ajaxRedirect('this', null, ['flash']);

            } catch (UniqueConstraintViolationException $e) {
                $message = "Uživatelský přístup `{$values->username}` exist, [error code {$e->getErrorCode()}]";
                $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, "Account update error", FlashMessageControl::TOAST_DANGER);
                $this->ajaxRedirect('this', null, ['flash']);
                return;
            }
        };


        $grid->addAction('delete', 'Smazat', 'deleteShop!')
            ->setIcon('trash')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat prodejnu `{$item['id']}`?";
            });


        $grid->addToolbarButton('addShop!', 'Přidat prodejnu')
            ->addAttributes([
                'data-target' => '#shopFormModal',
                'data-toggle' => 'ajax-modal',
                'data-title' => $this->translateMessage()->translate('reachPage.shop.add'),
            ])
            ->setClass('btn btn-xs btn-success')
            ->setIcon('fa fa-plus');


        return $grid;
    }



    protected function createComponentTargetGroupForm($name)
    {
        $form = $this->reachFacade->getTargetGroupFormFactory()->create();

        /** @var TargetGroupEntity $entity */
        if (!$this->editTargetGroup || !$entity = $this->reachFacade->getTargetGroupRepository()->find($this->editTargetGroup)) {
            $entity = new TargetGroupEntity("Nová skupina");
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
    protected function createComponentShopForm()
    {
        $form = $this->reachFacade->getShopFormFactory()->create();
        $form->setTranslator($this->translator->domain("messages.forms.shopForm"));

        $entity = null;
        if ($this->editShop) {
            $entity = $this->reachFacade->getShopRepository()->find($this->editShop);
        }

        if (!$entity) {
            $entity = new ShopEntity();
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
    protected function createComponentReachForm()
    {
        $form = $this->reachFacade->getReachFormFactory()->create();

        $targetGroups = $this->reachFacade->getTargetGroupRepository()->findPairs([], 'name');
        $shops = $this->reachFacade->getShopRepository()->findPairs([], 'name');
        $metricParams = $this->reachFacade->getMetricParamRepository()->findPairs([], 'name');

        dump($targetGroups);
        dump($shops);
        dump($metricParams);

        $entity = new MetricEntity();


        $form
            ->setShops($shops)
            ->setTargetGroups($targetGroups)
            ->setMetricParams($metricParams)
            ->create()
            ->bootstrap3Render()
            ->bindEntity($entity)
            ->onSuccess[] = function (BaseForm $form, $values) {

            $title   = $this->translateMessage()->translate('reachPage.shop.management');
            $message = $this->translateMessage()->translate('reachPage.shop.updated', null, ['name' => $form->getEntity()->id]);

            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
            $this->ajaxRedirect('this', null, ['flash']);
        };

        return $form;
    }


}