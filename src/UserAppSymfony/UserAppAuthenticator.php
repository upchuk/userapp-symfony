<?php

namespace UserAppSymfony;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\SimpleFormAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use UserApp\API as UserApp;
use UserApp\Exceptions\ServiceException;

class UserAppAuthenticator implements SimpleFormAuthenticatorInterface
{

  /**
   * @var UserApp
   */
  private $userAppClient;

  public function __construct(UserApp $userAppClient) {
    $this->userAppClient = $userAppClient;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
  {

    try {
      $login = $this->userAppClient->user->login(array(
        "login" => $token->getUsername(),
        "password" => $token->getCredentials(),
        )
      );

      // Load user from provider based on id
      $user = $userProvider->loadUserByLoginInfo($login);
    } catch(ServiceException $exception) {
      if ($exception->getErrorCode() == 'INVALID_ARGUMENT_LOGIN' || $exception->getErrorCode() == 'INVALID_ARGUMENT_PASSWORD') {
        throw new AuthenticationException('Invalid username or password');
      }
    }
    return new UserAppToken(
      $user,
      $user->getToken(),
      $providerKey,
      $user->getRoles()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function supportsToken(TokenInterface $token, $providerKey)
  {
    return $token instanceof UserAppToken
    && $token->getProviderKey() === $providerKey;
  }

  /**
   * {@inheritdoc}
   */
  public function createToken(Request $request, $username, $password, $providerKey)
  {
    return new UserAppToken($username, $password, $providerKey);
  }
}