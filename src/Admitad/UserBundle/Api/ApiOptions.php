<?php

namespace Admitad\UserBundle\Api;

class ApiOptions
{
    private $clientId;
    private $clientSecret;
    private $paths = [];

    public function __construct($clientId, $clientSecret, $paths = [])
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->paths = $paths;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    public function getPaths()
    {
        return $this->paths;
    }

    public function setPaths($paths)
    {
        $this->paths = $paths;
        return $this;
    }
}
