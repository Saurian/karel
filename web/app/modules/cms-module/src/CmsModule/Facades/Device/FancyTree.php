<?php


namespace CmsModule\Facades\Device;

use CmsModule\Entities\DeviceGroupEntity;
use Doctrine\Common\Collections\ArrayCollection;

class FancyTree
{

    /**
     * Recurse select tree structure
     *
     * 2 =>
     * name => "Morava Naše" (12)
     * active => false
     * unPlace => false
     * keywords => ""
     * id => 10
     * lft => 30
     * lvl => 1
     * rgt => 35
     * tag => "tagColor2" (9)
     * __children =>
     * 0 =>
     * name => "Břeclavsko" (11)
     * active => false
     * unPlace => false
     * keywords => null
     * id => 11
     * position => 9
     * category => ""
     * lft => 31
     * lvl => 2
     * rgt => 32
     * tag => null
     * __children => array ()
     * selected => true
     *
     * @param array $treeStructures
     * @param DeviceGroupEntity[] $devicesGroups
     * @param ArrayCollection $selectedDevicesGroups
     * @return mixed
     */
    public function selectByIds(array $treeStructures, array $ids)
    {
        foreach ($treeStructures as $index => $treeStructure) {
            if (in_array($treeStructure['id'], $ids)) {
                $treeStructures[$index]['selected'] = true;
            }

            if ($treeStructure['__children']) {
                $treeStructures[$index]['__children'] = $this->selectByIds($treeStructure['__children'], $ids);
            }
        }

        return $treeStructures;
    }


}