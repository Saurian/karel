<?php


namespace Devrun\CmsModule\Controls;

use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Repositories\DeviceGroupRepository;
use Flame\Application\UI\Control;
use Kdyby\Translation\Translator;
use Nette\Forms\Container;
use Nette\Security\User;
use Tracy\Debugger;
use Ublaboo\DataGrid\Utils\ItemDetailForm;

interface IDeviceGroupsTreeControlFactory
{
    /** @return DeviceGroupsTreeControl */
    function create();
}


class DeviceGroupsTreeControl extends Control
{

    /** @var Translator @inject */
    public $translator;

    /** @var User @inject */
    public $user;

    /** @var DeviceGroupRepository @inject */
    public $deviceGroupRepository;

    /** @var Container */
    protected $form;



    public function render($form = null)
    {
        $this->form = $form;

        $template = $this->getTemplate();

//        Debugger::barDump($form);

//        $template->form = $form;
        $template->render();

    }


    protected function createComponentDeviceGroupsTreeControl($name)
    {
        $grid = new DataGrid();
        $grid->setTranslator($this->translator);

        if ($this->user->isAllowed('Cms:Device', 'listAllDevices')) {
            $query = $this->deviceGroupRepository
                ->createQueryBuilder('e')
                ->select('e')
                ->andWhere('e.lvl = :level')->setParameter('level', 0)
                ->addOrderBy('e.lvl')
                ->addOrderBy('e.lft');

        } else {
            $rootDeviceGroupEntity = $this->deviceGroupRepository->getUserRootDeviceGroup($this->user);

            $query = $this->deviceGroupRepository
                ->createQueryBuilder('e')
                ->select('e')
                ->andWhere('e.lft > :left')->setParameter('left', $rootDeviceGroupEntity->getLft())
                ->andWhere('e.rgt < :right')->setParameter('right', $rootDeviceGroupEntity->getRgt())
                ->andWhere('e.root = :root')->setParameter('root', $rootDeviceGroupEntity)
                ->andWhere('e.lvl = :level')->setParameter('level', 1)
                ->addOrderBy('e.lvl')
                ->addOrderBy('e.lft');
        }


        $grid->setDataSource($query);
        $grid->setTreeView(function ($id) {
            return $this->deviceGroupRepository
                ->createQueryBuilder('e')
                ->where('e.parent = :parent')->setParameter('parent', $id)
                ->addOrderBy('e.lvl')
                ->addOrderBy('e.lft')
                ->getQuery()
                ->getResult();

        }, function (DeviceGroupEntity $deviceGroupEntity) {
            return $this->deviceGroupRepository->childCount($deviceGroupEntity) > 0;
        });


        $grid->addColumnText('name', 'Název')
             ->addAttributes(['class' => 'btn btn-xs btn-default btn-block']);


        $grid->addGroupAction('Aktivní')->onSelect[] = [$this, 'setActives'];

        $grid->setTemplateFile(__DIR__ . "/#datagrid_devices_groups_tree.latte");

        $grid->onRender[] = function (DataGrid $grid) use ($name) {
//                $grid->template->form = $this['usersGridControl']['items_detail_form'][$name];

//                Debugger::$maxDepth = 4;
//                Debugger::barDump($this['usersGridControl']->itemsDetail->getForm());
            /** @var ItemDetailForm $form */
//            $form = $this['usersGridControl']->itemsDetail->getForm();
//                $container = $form->getComponent(4);

//                $grid->template->form = $this['usersGridControl']->itemsDetail->getForm();
//                $grid->template->form = $container;


//            $grid->template->getLatte()->addProvider('formsStack', $form);
            $grid->template->_form = $this->form;

//            Debugger::barDump($grid->template->getLatte()->getProviders());
        };

        return $grid;

    }


}