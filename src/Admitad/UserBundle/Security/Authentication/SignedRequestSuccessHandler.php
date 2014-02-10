<?php

namespace Admitad\UserBundle\Security\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

class SignedRequestSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    protected function determineTargetUrl(Request $request)
    {
        if ($this->options['always_use_default_target_path']) {
            return $this->options['default_target_path'];
        }

        if ($request->query->has('retloc')) {
            $query = parse_url($request->query->get('retloc'), PHP_URL_QUERY);
            parse_str($query, $query);

            if (isset($query['path'])) {
                return $query['path'];
            }
        }

        return parent::determineTargetUrl($request);
    }
}
