<?php

/**
 * @file UserAppSymfony\Password\ChangePassword
 */

namespace UserAppSymfony\Password;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use UserApp\Exceptions\ServiceException;
use UserAppSymfony\Exception\PasswordChangeException;
use UserAppSymfony\UserAppUser;
use UserApp\API as UserApp;

class ChangePassword {

  /**
   * @var UserApp
   */
  private $userAppClient;

  /**
   * @var UserAppUser
   */
  private $currentUser;

  /**
   * Password change parameters
   * @var array
   */
  private $params = array();

  /**
   * Admin token if set as a parameter or NULL if not
   * @var string
   */
  private $admin_token;

  /**
   * @param UserApp $userAppClient
   * @param TokenStorageInterface $tokenStorage
   * @param $admin_token
   */
  public function __construct(UserApp $userAppClient, TokenStorageInterface $tokenStorage, $admin_token) {
    $this->userAppClient = $userAppClient;
    $this->currentUser = $tokenStorage->getToken()->getUser();
    $this->admin_token = $admin_token;
  }

  /**
   * Changes the password of the current user
   * @param $params
   * @return bool|true
   * @throws PasswordChangeException
   */
  public function changePassword($params) {
    $resolver = new OptionsResolver();
    $resolver->setDefaults(array(
      'old_password' => null,
      'new_password' => null,
      'token' => null
    ));
    $resolver->setRequired('new_password');
    $resolver->setDefault('type', function (Options $options) {
      if ($options['old_password'] !== null) {
        return 'password';
      }
      return 'token';
    });

    if ( ! isset($params['old_password'])) {
      $resolver->setRequired('token');
    }

    $this->params = $resolver->resolve($params);

    return $this->params['type'] == 'password' ? $this->changeByPassword() : $this->changeByToken();
  }

  /**
   * Resets the current user's password using the admin token and returns a
   * temporary token to be used with $this->changeByToken().
   *
   * @param $username
   * @return string or bool
   * @throws PasswordChangeException
   */
  public function resetPassword($username) {
    if ( ! $this->admin_token) {
      throw new PasswordChangeException('No admin token set.');
    }
    $this->userAppClient->setOption('token', $this->admin_token);

    try {
      $result = $this->userAppClient->user->resetPassword(array(
        "login" => $username,
      ));

      return isset($result->password_token) ? $result->password_token : false;
    }
    catch (ServiceException $exception) {
      if ($exception->getErrorCode() == 'INVALID_ARGUMENT_LOGIN') {
        throw new PasswordChangeException('User not found.');
      }
    }
  }

  /**
   * Changes the password using a new and old password.
   *
   * @return bool|true
   * @throws PasswordChangeException
   */
  private function changeByPassword() {
    $this->userAppClient->setOption('token', $this->currentUser->getToken());

    try {
      $this->userAppClient->user->changePassword(array(
        "new_password" => $this->params['new_password'],
        "current_password" => $this->params['old_password'],
        "user_id" => $this->currentUser->getId(),
      ));

      return true;
    }
    catch (ServiceException $exception) {
      if ($exception->getErrorCode() == 'INVALID_ARGUMENT_CURRENT_PASSWORD') {
        throw new PasswordChangeException('Invalid current password.');
      }
      if ($exception->getErrorCode() == 'INVALID_ARGUMENT_USER_ID') {
        throw new PasswordChangeException('User not found.');
      }
    }
  }

  /**
   * Changes the password using a new password and temporary token.
   *
   * @return bool
   * @throws PasswordChangeException
   */
  private function changeByToken() {
    $this->userAppClient->setOption('token', $this->currentUser->getToken());

    try {
      $this->userAppClient->user->changePassword(array(
        "new_password" => $this->params['new_password'],
        "password_token" => $this->params['token'],
      ));

      return true;
    }
    catch (ServiceException $exception) {
      if ($exception->getErrorCode() == 'INVALID_ARGUMENT_PASSWORD_TOKEN') {
        throw new PasswordChangeException('Invalid token.');
      }
    }
  }

}