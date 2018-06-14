<?php
namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Container;

class JWTUser implements JWTUserInterface
{
    protected $password;
    protected $username;
    protected $roles;

    public function __construct($username, array $roles)
    {
//        var_dump()
//        dd($username);
        $this->username = $username;
        $this->roles = $roles;
    }

    public static function createFromPayload($username, array $payload)
    {
        return new self(
            $username,
            $payload['roles']
        );
    }

    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @todo:: db query
     * @return null
     */
    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
        return null;
    }
}
