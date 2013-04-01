<?php

namespace Dsnet\Sf1SecurityBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

class Sf1Listener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    public function handle(GetResponseEvent $event)
    {
        try {
            // empty or incorrect session information
            if (!isset($_SESSION['symfony/user/sfUser/authenticated']) || $_SESSION['symfony/user/sfUser/authenticated'] !== true)
                throw new AuthenticationException('not authenthicated in session');

            // no ordinary_user credential - no access
            if (!isset($_SESSION['symfony/user/sfUser/credentials']) || !is_array($_SESSION['symfony/user/sfUser/credentials']) || !in_array('ordinary_user', $_SESSION['symfony/user/sfUser/credentials'], true))
                throw new AuthenticationException('no credentials in session');

            // more than a hour passed since last request - do not authenthicate
            if (!isset($_SESSION['symfony/user/sfUser/lastRequest']) || time() - $_SESSION['symfony/user/sfUser/lastRequest'] > 3600)
                throw new AuthenticationException('session timeout');

            $user_attributes = $_SESSION['symfony/user/sfUser/attributes'];
            $user_attributes = $user_attributes['symfony/user/sfUser/attributes'];

            if (!isset($user_attributes['username']))
                throw new AuthenticationException('no username in session');

            $username = $user_attributes['username'];

            $token = new PreAuthenticatedToken($username, null, 'main', array('ROLE_USER'));
            $token->setUser($username);

            $authToken = $this->authenticationManager->authenticate($token);

            $this->securityContext->setToken($authToken);
        } catch (AuthenticationException $failed) {
            $this->securityContext->setToken(null);

            // Redirect to landing page
            $response = new RedirectResponse('/');
            $event->setResponse($response);
        }
    }
}
