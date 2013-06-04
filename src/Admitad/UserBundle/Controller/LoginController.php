<?php

namespace Admitad\UserBundle\Controller;

use Admitad\Api\Api;
use Admitad\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        $user = $this->getUserManager()->findUserBy(['admitadId' => $data['id']]);
        if (!$user) {
            $user = $this->getUserManager()->createUser();
            $user->setAdmitadId($data['id']);
            $user->setUsername($data['username']);
        }

        $user->setFirstName($data['first_name']);
        $user->setLastName($data['last_name']);
        $user->setAdmitadAccessToken($data['access_token']);
        $user->setAdmitadRefreshToken($data['refresh_token']);
        $user->setAdmitadTokenExpireIn($data['expires_in']);

        $this->getUserManager()->updateUser($user);

        $this->loginUser($user);

        return $this->redirect($this->generateUrl('index'));
    }

    protected function loginUser(User $user)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $token->setUser($user);

        $this->container->get('security.context')->setToken($token);

        // Since we're "faking" normal login, we need to throw our INTERACTIVE_LOGIN event manually
        $this->container->get('event_dispatcher')->dispatch(
            SecurityEvents::INTERACTIVE_LOGIN,
            new InteractiveLoginEvent($this->getRequest(), $token)
        );
    }

    /**
     * @return UserManagerInterface
     */
    protected function getUserManager()
    {
        return $this->container->get('fos_user.user_manager');
    }
}
