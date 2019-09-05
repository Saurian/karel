<?php
/**
 * This file is part of karl.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    ApiPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Presenters;

use CmsModule\Controls\FlashMessageControl;
use CmsModule\Entities\DeviceLogEntity;
use CmsModule\Repositories\DeviceLogRepository;
use CmsModule\Repositories\LogRepository;
use Nette\Utils\Html;
use Tracy\Debugger;
use Ublaboo\DataGrid\DataGrid;

class ApiPresenter extends BasePresenter
{

    /** @var LogRepository @inject */
    public $logRepository;

    /** @var DeviceLogRepository @inject */
    public $deviceLogRepository;

    /** @var int @persistent */
    public $tableRowList = 1;


    public function handleDelete($id)
    {
        if ($entity = $this->deviceLogRepository->find($id)) {
            $this->deviceLogRepository->getEntityManager()->remove($entity)->flush();

            $title = $this->translator->domain('messages')->translate('apiPage.removeItem.title');
            $this->flashMessage($this->translator->domain('messages')->translate("apiPage.removeItem.text", null, ['id' => $id]), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
        }

        $this->ajaxRedirect();
    }


    public function handleTableRowList()
    {
        $this->tableRowList = !$this->tableRowList;
        $this->ajaxRedirect();
    }



    protected function createComponentApiLogsGridControl()
    {
        $grid = new DataGrid();
        $data = $this->deviceLogRepository->createQueryBuilder('a')
            ->addSelect('d')
            ->join('a.device', 'd');

        $grid->setDataSource($data);
        $grid->setItemsPerPageList([20, 30, 50, 100]);

        $grid->addColumnDateTime('inserted', 'Vložen')
            ->setSortable()
            ->setFormat('n. j. Y H:i')
            ->setAlign('center')
            ->setFitContent()
            ->setFilterDateRange();


        $grid->addColumnNumber('deviceId', 'ID zařízení', 'device.sn')
            ->setSortable()
            ->setSortableCallback(function (\Kdyby\Doctrine\QueryBuilder $queryBuilder, $sort) {
                $queryBuilder->addOrderBy('d.sn', $sort['device.sn']);
            })
            ->setFitContent()
            ->setFilterText()
            ->setCondition(function (\Kdyby\Doctrine\QueryBuilder $queryBuilder, $value) {
                $queryBuilder->andWhere('d.sn LIKE :sn')->setParameter('sn', "%$value%");
            });

        $grid->addColumnNumber('device', 'Zařízení')
            ->setSortable()
            ->setSortableCallback(function (\Kdyby\Doctrine\QueryBuilder $queryBuilder, $sort) {
                $queryBuilder->addOrderBy('d.name', $sort['device']);
            })
            ->setFilterText()
            ->setCondition(function (\Kdyby\Doctrine\QueryBuilder $queryBuilder, $value) {
                $queryBuilder->andWhere('d.name LIKE :name')->setParameter('name', "%$value%");
            });


        $grid->addColumnNumber('ssid', 'SSID')
            ->setSortable()
//            ->setFitContent()
            ->setFilterText();

        $grid->addColumnNumber('command', 'Dotaz')
            ->setSortable()
            ->setFitContent()
            ->setFilterText();


        $grid->addColumnNumber('reason', 'Důvod zamítnutí')
            ->setSortable()
//            ->setFitContent()
            ->setFilterText();

        $grid->addColumnNumber('valid', 'Validní')
            ->setRenderer(function (DeviceLogEntity $row) {
                $icon = $row->isValid()
                    ? "<i class=\"fa fa-check\" aria-hidden=\"true\"></i>"
                    : "<i class=\"fa fa-close\" aria-hidden=\"true\"></i>";

                $color = $row->isValid()
                    ? "label label-success"
                    : "label label-danger";

                $result = Html::el('span')
                    ->setHtml($icon)
                    ->setAttribute('class', $color);

                return $result;
            })
            ->setSortable()
            ->setFitContent()
            ->setFilterSelect([null => 'Všechny', 0 => 'nevalidní', 1 => 'validní']);

        $grid->addAction('delete', 'Smazat', 'delete!')
            ->setIcon('trash fa-2x')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat záznam [id: {$item->id}]?";
            });

        $grid->addToolbarButton('tableRowList!', $this->tableRowList ? 'Tabulkový list' : 'Textový list');


        if ($this->tableRowList) {
            $grid->setItemsDetail(__DIR__ . '/templates/DataGrid/api_log_grid_one-detail.latte');

        } else {
            $grid->setItemsDetail(__DIR__ . '/templates/DataGrid/api_log_grid_detail.latte');
        }
        $grid->setTranslator($this->translator);
        return $grid;
    }


    protected function createComponentLogsGridControl()
    {
        $grid = new DataGrid();
        $data = $this->logRepository->createQueryBuilder('a');
        $grid->setDataSource($data);
        $grid->setItemsPerPageList([20, 30, 50, 100]);

        $grid->addColumnNumber('id', 'ID')
            ->setSortable()
            ->setAlign('center')
            ->setFitContent();

        $grid->addColumnText('targetKey', 'EntityID')
            ->setSortable()
            ->setFitContent()
            ->setAlign('center')
            ->setFilterText();


        $grid->addColumnDateTime('inserted', 'Vložen')
            ->setSortable();
//            ->setFilterDateRange();


        $grid->addColumnText('role', 'Role')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('type', 'Typ')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('action', 'Akce')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('message', 'Zpráva')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnNumber('level', 'Úroveň')
            ->setSortable()
            ->setFitContent()
            ->setFilterText();


        $grid->addAction('delete', 'Smazat', 'delete!')
            ->setIcon('trash fa-2x')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat záznam [id: {$item->id}]?";
            });


        $grid->setItemsDetail(__DIR__ . '/templates/DataGrid/log_grid_detail.latte');
        $grid->setTranslator($this->translator);
        return $grid;
    }


    public function getHtmlResult($data)
    {
        return Debugger::dump($data, true);
    }

}