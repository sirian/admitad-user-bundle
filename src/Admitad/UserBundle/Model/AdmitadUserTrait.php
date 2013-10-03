<?php

namespace Admitad\UserBundle\Model;

use Doctrine\ORM\Mapping as ORM;


trait AdmitadUserTrait
{
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
}
