<?php

/**
 * @file UserAppSymfony\UserAppLogout
 */

namespace UserAppSymfony;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use UserApp\API as UserApp;
use UserApp\Exceptions\ServiceException;

class UserAppLogout implements LogoutHandlerInterface {

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
  public function logout(Request $request, Response $response, TokenInterface $token) {
    $api = $this->userAppClient;
    $user = $token->getUser();
    $api->setOption('token', $user->getToken());
    try {
      $api->user->logout();
    }
    catch (ServiceException $exception) {
      // Empty for now, error probably caused by user not being authenticated which means
      // user is logged out already.
    }
  }
}
