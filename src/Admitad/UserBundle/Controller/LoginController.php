<?php

namespace Admitad\UserBundle\Controller;

use Admitad\Api\Api;
use Admitad\Api\Exception\InvalidSignedRequestException;
use Admitad\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Security\LoginManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LoginController extends Controller
{
    public function admitadStoreLoginAction(Request $request)
    {
        $api = new Api();
        try {
            $data = $api->parseSignedRequest(
                $request->query->get('signed_request'),
                $this->container->getParameter('admitad_user.client_secret')
            );
        } catch (InvalidSignedRequestException $e) {
            throw new AccessDeniedHttpException("Invalid signed request");
        }

        return $this->authUser($data);
    }

    public function admitadOAuthLoginAction()
    {
        $api = new Api();
        return $this->redirect($api->getAuthorizeUrl(
            $this->container->getParameter('admitad_user.client_id'),
            $this->generateUrl('login_admitad_oauth_check', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->container->getParameter('admitad_user.scope')
        ));
    }

    public function admitadOAuthLoginCheckAction(Request $request)
    {
        $code = $request->query->get('code');
        if (!$code) {
            throw new AccessDeniedHttpException('Authorization failed');
        }

        $api = new Api();
        $data = $api->requestAccessToken(
            $this->container->getParameter('admitad_user.client_id'),
            $this->container->getParameter('admitad_user.client_secret'),
            $code,
            $this->generateUrl('login_admitad_oauth_check', [], UrlGeneratorInterface::ABSOLUTE_URL)
        )->getResult();

        return $this->authUser($data);
    }

    protected function authUser($data)
    {
        /**
         * @var \Admitad\DirectBundle\Entity\User $user
         */
        $user = $this->getUserManager()->findUserBy(['admitadId' => $data['id']]);
        if (!$user) {
            $user = $this->getUserManager()->createUser();
            $user->setEnabled(true);
        }

        $api = new Api($data['access_token']);
        $me = $api->me()->getResult();

        if (isset($me['email'])) {
            $user->setEmail($me['email']);
        }
        $user->setAdmitadId($me['id']);
        $user->setUsername($me['username']);
        $user->setFirstName($me['first_name']);
        $user->setLastName($me['last_name']);
        $user->setAdmitadAccessToken($data['access_token']);
        $user->setAdmitadRefreshToken($data['refresh_token']);
        $user->setAdmitadTokenExpireIn($data['expires_in']);

        $this->getUserManager()->updateUser($user);

        $response = $this->redirect($this->generateUrl('index'));

        $this->getLoginManager()->loginUser('main', $user, $response);

        return $response;
    }

    /**
     * @return LoginManagerInterface
     */
    protected function getLoginManager()
    {
        return $this->container->get('fos_user.security.login_manager');
    }

    /**
     * @return UserManagerInterface
     */
    protected function getUserManager()
    {
        return $this->container->get('fos_user.user_manager');
    }
}
