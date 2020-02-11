<?php


namespace CmsModule\Repositories;


trait UtilRepository
{

    static function assocObjects($entities = [])
    {
        $result = [];
        foreach ($entities as $entity) {
            $result[$entity->id] = $entity;
        }

        return $result;
    }

    static function assocObjectsId($entities = [])
    {
        $result = [];
        foreach ($entities as $entity) {
            $result[$entity->id] = $entity->id;
        }

        return $result;
    }

    static function objects($entities = [], $key = 'id')
    {
        $result = [];
        foreach ($entities as $entity) {
            $result[] = $entity->{$key};
        }

        return $result;
    }

    static function objectsId($entities = [])
    {
        $result = [];
        foreach ($entities as $entity) {
            $result[] = $entity->id;
        }

        return $result;
    }


}