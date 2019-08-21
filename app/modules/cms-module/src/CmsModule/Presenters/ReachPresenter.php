<?php


namespace CmsModule\Presenters;


use Ublaboo\DataGrid\DataGrid;

class ReachPresenter extends BasePresenter
{

    public function renderDefault()
    {

    }


    /**
     * @return DataGrid
     */
    protected function createComponentReachGridControl()
    {
        $grid = new DataGrid();
        $grid->setTranslator($this->translator);

        $data = [
            ['id' => 1, 'title' => 'Ahoj'],
            ['id' => 2, 'title' => 'Behoj'],
        ];

        $grid->setDataSource($data);


        $grid->addColumnText('id', 'ID')
            ->setSortable()
            ->setFitContent()
            ->setFilterText();

        $grid->addColumnText('title', 'Title')
            ->setSortable()
            ->setFitContent()
            ->setFilterText();



        return $grid;
    }


}