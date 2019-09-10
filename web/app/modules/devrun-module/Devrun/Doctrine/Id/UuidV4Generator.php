<?php
/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    UuidV4Generator.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\Id;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;

class UuidV4Generator extends AbstractIdGenerator
{

    /**
     * Generates an identifier for an entity.
     * on the PHP manual
     *
     * @param EntityManager|EntityManager  $em
     * @param \Doctrine\ORM\Mapping\Entity $entity
     *
     * @return string
     */
    public function generateV4(EntityManager $em, $entity) {
        return self::gen_uuid();
    }

    /**
     * @thanks https://www.php.net/manual/en/function.uniqid.php#94959
     *
     * @return string
     */
    static function generateUuidV4() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    /**
     * Generates an identifier for an entity.
     * According to RFC 4122 - Section 4.4
     *
     * @param EntityManager|EntityManager  $em
     * @param \Doctrine\ORM\Mapping\Entity $entity
     *
     * @return string
     */
    public function generate(EntityManager $em, $entity)
    {
        $data = openssl_random_pseudo_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}