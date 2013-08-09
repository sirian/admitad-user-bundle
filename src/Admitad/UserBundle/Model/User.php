<?php

namespace Admitad\UserBundle\Model;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class User extends BaseUser implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="first_name", type="string", length=255)
     */
    protected $firstName = '';

    /**
     * @ORM\Column(name="last_name", type="string", length=255)
     */
    protected $lastName = '';

    /**
     * @ORM\Column(type="integer", name="admitad_id", unique=true)
     */
    protected $admitadId = 0;

    /**
     * @ORM\Column(type="string", name="admitad_access_token", length=255)
     */
    protected $admitadAccessToken = '';

    /**
     * @ORM\Column(type="string", name="admitad_refresh_token", length=255)
     */
    protected $admitadRefreshToken = '';

    /**
     * @ORM\Column(type="integer", name="admitad_token_expire")
     */
    protected $admitadTokenExpire = 0;

    public function __construct()
    {
        parent::__construct();
        $this->generateRandomEmail();
        $this->password = '';
    }

    public function getAdmitadId()
    {
        return $this->admitadId;
    }

    public function setAdmitadId($admitadId)
    {
        $this->admitadId = (int)$admitadId;
    }

    public function getAdmitadAccessToken()
    {
        return $this->admitadAccessToken;
    }

    public function setAdmitadAccessToken($token)
    {
        $this->admitadAccessToken = (string)$token;
    }

    public function getAdmitadRefreshToken()
    {
        return $this->admitadRefreshToken;
    }

    public function setAdmitadRefreshToken($token)
    {
        $this->admitadRefreshToken = (string)$token;
    }

    private function generateRandomEmail()
    {
        $this->email = sha1(uniqid(mt_rand())) . '@fake.fake';
    }

    public function isFakeEmail()
    {
        return strpos($this->email, '@fake.fake') !== false;
    }

    public function getAdmitadTokenExpire()
    {
        return $this->admitadTokenExpire;
    }

    public function getAdmitadTokenExpireIn()
    {
        return max(0, $this->admitadTokenExpire - time());
    }

    public function setAdmitadTokenExpire($expire)
    {
        $this->admitadTokenExpire = $expire;
    }

    public function setAdmitadTokenExpireIn($expireIn)
    {
        $this->setAdmitadTokenExpire(time() + $expireIn);
    }

    public function isAdmitadTokenExpired()
    {
        return $this->getAdmitadTokenExpireIn() <= 0;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = (string)$firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = (string)$lastName;
    }
}
