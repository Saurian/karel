<?php

declare(strict_types=1);

namespace CmsModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity;


/**
 * @ORM\Entity()
 * @ORM\Table(name="identity")
 */
class Identity implements IIdentity {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="email", type="string", length=127, unique=true)
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(name="password_hash", type="string", length=127)
     * @var string
     */
    private $passwordHash;

    /**
     * @ORM\Column(name="name", type="string", length=127, unique=true)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="roles", type="simple_array", nullable=true)
     * @var string[]
     */
    private $roles;




    public function getId() : int {
        return $this->id;
    }


    public function getEmail() : string {
        return $this->email;
    }

    public function setEmail(string $email) : Identity {
        if (empty($email)) {
            throw new \InvalidArgumentException('E-mail address cannot be empty');
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid e-mail address: ' . $email);
        }

        $this->email = $email;
        return $this;
    }


    public function areCredentialsValid(array $credentials) : bool {
        if (count($credentials) !== 2) {
            return false;
        }

        list($email, $password) = $credentials;
        return $email === $this->email && password_verify($password, $this->passwordHash);
    }

    public function setPassword(string $password) : Identity {
        $this->passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        return $this;
    }


    public function getName() : string {
        return $this->name;
    }

    public function setName(string $name) : Identity {
        if (empty($name)) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }

        $this->name = $name;
        return $this;
    }


    public function setRoles($roles) : Identity {
        if (!$roles) {
            $this->roles = null;
        } else if (!is_array($roles)) {
            $this->roles = explode(',', $roles);
        } else {
            $this->roles = $roles;
        }

        return $this;
    }

    public function getRoles() : array {
        return $this->roles ?: [];
    }

}
