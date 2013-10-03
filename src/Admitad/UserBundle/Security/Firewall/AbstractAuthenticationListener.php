<?php

namespace Admitad\UserBundle\Security\Firewall;

use Admitad\UserBundle\Api\ApiOptions;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener as BaseAbstractAuthenticationListener;

abstract class AbstractAuthenticationListener extends BaseAbstractAuthenticationListener
{
    /**
     * @var ApiOptions
     */
    protected $apiOptions;

    public function setApiOptions($apiOptions)
    {
        $this->apiOptions = $apiOptions;
        return $this;
    }
}
