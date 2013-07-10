<?php

namespace Admitad\UserBundle\Controller;

use Admitad\Api\Api;
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
        $signedRequest = $request->query->get('signed_request');
        list ($key, $data) = explode('.', $signedRequest);

        $hash = hash_hmac('sha256', $data, $this->container->getParameter('admitad_user.client_secret'));
        if ($hash != $key) {
            throw new AccessDeniedHttpException("Invalid signed request");
        }
        $data = json_decode(base64_decode($data), true);

        return $this->authUser($data);
    }

    public function admitadOAuthLoginAction()
    {
        $query = [
            'scope' => $this->container->getParameter('admitad_user.scope'),
            'client_id' => $this->container->getParameter('admitad_user.client_id'),
            'redirect_uri' => $this->generateUrl('login_admitad_oauth_check', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'response_type' => 'code'
        ];

        return $this->redirect('https://api.admitad.com/authorize/?' . http_build_query($query));
    }

    public function admitadOAuthLoginCheckAction(Request $request)
    {
        $code = $request->query->get('code');
        if (!$code) {
            throw new \InvalidArgumentException('Authorization failed');
        }

        $api = new Api();
        $data = $api->requestAccessToken(
            $this->container->getParameter('admitad_user.client_id'),
            $this->container->getParameter('admitad_user.client_secret'),
            $code,
            $this->generateUrl('login_admitad_oauth_check', [], UrlGeneratorInterface::ABSOLUTE_URL)
        )->getArrayResult();

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
        }

        $api = new Api($data['access_token']);
        $me = $api->get('/me/')->getArrayResult();

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
