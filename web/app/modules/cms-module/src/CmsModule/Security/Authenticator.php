<?php

namespace CmsModule\Security;

use CmsModule\Repositories\UserRepository;
use Kdyby\Translation\ITranslator;
use Kdyby\Translation\Translator;
use Nette;


/**
 * Users authenticator.
 */
class Authenticator implements Nette\Security\IAuthenticator
{
    const
        COLUMN_ID = 'id',
        COLUMN_NAME = 'username',
        COLUMN_ROLE = 'role',
        COLUMN_ACTIVE = 'active',
        COLUMN_PASSWORD_HASH = 'password',
        COLUMN_NEW_PASSWORD_HASH = 'newPassword',
        COLUMN_MEMBER = 'member',
        COLUMN_MEMBER_ID = 'memberId';

    /** @var UserRepository */
    private $userRepository;

    /** @var Translator */
    private $translator;

    private $translateMessage = false;

    /**
     * Authenticator constructor.
     *
     * @param UserRepository $userRepository
     * @param ITranslator    $translator
     */
    function __construct(UserRepository $userRepository, ITranslator $translator)
    {
        $this->userRepository = $userRepository;
        $this->translator = $translator;
    }


    /**
     * Performs an authentication.
     *
     * @param array $credentials
     *
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        if (count($credentials) == 2) {
            list($username, $password) = $credentials;

        } elseif (count($credentials) == 1) {
            list($username) = $credentials;
            $password = null;

        } else {
            $username = null;
            $password = null;
        }

        /** @var $row array */
        $row = $this->userRepository->findByLogin($username);

        if (!$row) {
            $msg = "invalid_login_information";
            throw new Nette\Security\AuthenticationException($this->translateMessage
                ? $this->translator->translate($msg)
                : $msg
                , self::IDENTITY_NOT_FOUND);

        } elseif ($username !== $row[self::COLUMN_NAME]) {
            $msg = "invalid_login_information";
            throw new Nette\Security\AuthenticationException($this->translateMessage
                ? $this->translator->translate($msg)
                : $msg
                , self::INVALID_CREDENTIAL);

        } elseif ((md5($username . $password) !== $row[self::COLUMN_PASSWORD_HASH]) && ($password !== $row[self::COLUMN_PASSWORD_HASH])) {
            $msg = "invalid_login_information";
            throw new Nette\Security\AuthenticationException($this->translateMessage
                ? $this->translator->translate($msg)
                : $msg
                , self::INVALID_CREDENTIAL);

        } elseif (!$row[self::COLUMN_ACTIVE]) {
            $msg = "account_disable";
            throw new Nette\Security\AuthenticationException($this->translateMessage
                ? $this->translator->translate($msg)
                : $msg
                , self::NOT_APPROVED);
        }

        $arr = $row;

        unset($arr[self::COLUMN_PASSWORD_HASH]);
        unset($arr[self::COLUMN_NEW_PASSWORD_HASH]);
        return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
    }

}

class DuplicateNameException extends \Exception
{
}