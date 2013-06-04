<?php

namespace Admitad\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AdmitadUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
