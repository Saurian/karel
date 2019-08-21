<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    MediaRepository.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories;


use CmsModule\Repositories\Queries\MediaQuery;
use Devrun\Doctrine\DoctrineForms\ToManyContainer;
use Kdyby\Doctrine\EntityRepository;
use Nette\Http\Session;

class MediaRepository extends EntityRepository
{

    const SESSION_NAME = 'mediaStorage';

    /**
     * @var Session
     */
    private $session;

    /**
     * MediaRepository constructor.
     *
     * @param Session $session
     */
    public function injectSession(Session $session)
    {
        $this->session = $session;
    }



    public function saveMedia($media)
    {
        $section = $this->getSection();
        $section->media = $media;
    }


    public function addMedium($medium)
    {
        $section = $this->getSection();

        $count = isset($section->media) ? count($section->media) : 0;
        $id = ToManyContainer::NEW_PREFIX . ($count);


//        $section->media[$id] = $medium;
        $section->media[] = $medium;
    }


    public function clearMedia()
    {
        $section = $this->getSection();
        unset($section->media);
        unset($section->templateName);
    }

    public function removeMedium($index)
    {
        if ($this->existMedia()) {
            $media = $this->getMedia();

            unset($media[$index]);
            $this->clearMedia();

            foreach ($media as $medium) {
                $this->addMedium($medium);
            }

        }
    }


    public function existMedia()
    {
        $section = $this->getSection();
        return isset($section->media);
    }

    public function getMedia()
    {
        $section = $this->getSection();
        return (array) $section->media;
    }

    public function hasTemplateName()
    {
        $section = $this->getSection();
        return isset($section->templateName);
    }

    public function getTemplateName()
    {
        $section = $this->getSection();
        return $section->templateName;
    }

    public function setTemplateName($name)
    {
        $section = $this->getSection();
        $section->templateName = $name;
    }


    /**
     * @return \Nette\Http\SessionSection
     */
    public function getSection()
    {
        return $this->session->getSection(self::SESSION_NAME);
    }


    /**
     * return max position in category
     *
     * @param $category
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return mixed
     */
    public function getMaxPositionInCategory($category)
    {
        $result = $this->createQueryBuilder('e')
            ->select('max(e.position)')
            ->where('e.category = ?1')->setParameter(1, $category)
            ->getQuery()
            ->getSingleScalarResult();

        return intval($result);
    }


    public function getMediaQuery()
    {
        $query = (new MediaQuery());
        $query
            ->withDevicesCampaigns();

        return $query;
    }


}