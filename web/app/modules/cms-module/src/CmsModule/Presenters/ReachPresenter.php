<?php


namespace CmsModule\Presenters;


use CmsModule\Controls\FlashMessageControl;
use CmsModule\Entities\TargetGroupEntity;
use CmsModule\Entities\UsersGroupEntity;
use CmsModule\Facades\ReachFacade;
use CmsModule\Forms\BaseForm;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\TextInput;
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




    public function renderDefault()
    {

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


        $grid->addAction('edit', 'Edit', 'editTargetGroup!')
            ->setIcon('pencil')
            ->setDataAttribute('target', '#targetGroupFormModal')
            ->setDataAttribute('title', $this->translateMessage()->translate('devicePage.editDevice'))
            ->setTitle($this->translateMessage()->translate('devicePage.editDevice'))
            ->setClass('ajax-modal btn btn-xs btn-info');


        $grid->addAction('delete', 'Smazat', 'deleteTargetGroup!')
            ->setIcon('trash')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat cílovou skupinu `{$item->name}`?";
            });


        $grid->addToolbarButton('addTargetGroup!', 'Přidat cílovou skupinu')
            ->addAttributes([
                'data-target' => '#targetGroupFormModal',
                'data-title' => $this->translateMessage()->translate('devicePage.edit_device_group'),
            ])
            ->setClass('ajax-modal btn btn-xs btn-success')
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


}