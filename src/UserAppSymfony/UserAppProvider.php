<?php

/**
 * @file UserAppSymfony\UserAppProvider
 */

namespace UserAppSymfony;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use UserApp\API as UserApp;
use UserApp\Exceptions\ServiceException;
use UserAppSymfony\Exception\NoUserRoleException;
use UserAppSymfony\UserAppUser;

class UserAppProvider implements UserProviderInterface
{
  /**
   * @var UserApp
   */
  private $userAppClient;

  /**
   * @var integer
   */
  private $heartbeat_frequency;

  /**
   * @param UserApp $userAppClient
   * @param $heartbeat_frequency
   */
  public function __construct(UserApp $userAppClient, $heartbeat_frequency) {
    $this->userAppClient = $userAppClient;
    $this->heartbeat_frequency = $heartbeat_frequency;
  }

  /**
   * {@inheritdoc}
   */
  public function loadUserByUsername($username)
  {
    // Empty for now
  }

  /**
   * {@inheritdoc}
   */
  public function refreshUser(UserInterface $user)
  {
    if (!$user instanceof UserAppUser) {
      throw new UnsupportedUserException(
        sprintf('Instances of "%s" are not supported.', get_class($user))
      );
    }

    // Perform heartbeat request after X number of minutes from the previous one
    // Defaults to 45 minutes if no userapp_heartbeat_frequency parameter is set
    if ($user->getLastHeartbeat() == null || time() > $user->getLastHeartbeat() + $this->heartbeat_frequency) {
      try {
        $api = $this->userAppClient;
        $api->setOption('token', $user->getToken());
        $api->token->heartbeat();
        $user->unlock();
        $user->setLastHeartbeat(time());
      }
      catch (ServiceException $exception) {
        if ($exception->getErrorCode() == 'INVALID_CREDENTIALS') {
          throw new AuthenticationException('Invalid credentials');
        }
        if ($exception->getErrorCode() == 'AUTHORIZATION_USER_LOCKED') {
          $user->lock();
        }
      }
    }

    return $user;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsClass($class)
  {
    return $class === 'UserAppSymfony\UserAppUser';
  }

  /**
   *
   * Loads a user from UserApp.io based on a successful login response.
   *
   * @param $login
   * @return UserAppUser
   * @throws NoUserRoleException
   */
  public function loadUserByLoginInfo($login) {

    try {
      $api = $this->userAppClient;
      $api->setOption('token', $login->token);
      $users = $api->user->get();
    } catch(ServiceException $exception) {
      if ($exception->getErrorCode() == 'INVALID_ARGUMENT_USER_ID') {
        throw new UsernameNotFoundException(sprintf('User with the id "%s" not found.', $login->user_id));
      }
    }

    if ( ! empty($users)) {
      return $this->userFromUserApp($users[0], $login->token);
    }
  }

  /**
   * Creates a UserAppUser from a user response from UserApp.io
   *
   * @param $user
   * @param $token
   * @return UserAppUser
   * @throws NoUserRoleException
   */
  private function userFromUserApp($user, $token) {

    $roles = $this->extractRolesFromPermissions($user);

    $options = array(
      'id' => $user->user_id,
      'username' => $user->login,
      'token' => $token,
      'firstName' => $user->first_name,
      'lastName' => $user->last_name,
      'email' => $user->email,
      'roles' => $roles,
      'properties' => $user->properties,
      'features' => $user->features,
      'permissions' => $user->permissions,
      'created' => $user->created_at,
      'locked' => !empty($user->locks),
      'last_logged_in' => $user->last_login_at,
      'last_heartbeat' => time(),
    );

    return new UserAppUser($options);
  }

  /**
   * Extracts the roles from the permissions list of a user
   *
   * @param $user
   * @return array
   * @throws NoUserRoleException
   */
  private function extractRolesFromPermissions($user) {
    $permissions = get_object_vars($user->permissions);
    if (empty($permissions)) {
      throw new NoUserRoleException('There are no roles set up for your users.');
    }
    $roles = array();
    foreach ($permissions as $role => $permission) {
      if ($permission->value === TRUE) {
        $roles[] = $role;
      }
    }

    if (empty($roles)) {
      throw new NoUserRoleException('This user has no roles enabled.');
    }

    return $roles;
  }
}
