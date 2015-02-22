<?php

/**
 * @file UserAppSymfony\UserAppUser
 */

namespace UserAppSymfony;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;

class UserAppUser implements UserInterface {

  private $id;
  private $username;
  private $token;
  private $firstName;
  private $lastName;
  private $email;
  private $roles;
  private $properties;
  private $features;
  private $permissions;
  private $created;
  private $locked;
  private $last_logged_in;
  private $last_heartbeat;

  public function __construct($options)
  {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);

    $params = $resolver->resolve($options);
    foreach ($params as $property => $value) {
      $this->{$property} = $value;
    }
  }

  /**
   * Configures the class options
   *
   * @param $resolver OptionsResolver
   */
  private function configureOptions($resolver)
  {
    $resolver->setDefaults(array(
      'id' => NULL,
      'username' => NULL,
      'token' => NULL,
      'firstName' => NULL,
      'lastName' => NULL,
      'email' => NULL,
      'roles' => array(),
      'properties' => array(),
      'features' => array(),
      'permissions' => array(),
      'created' => NULL,
      'locked' => NULL,
      'last_logged_in' => NULL,
      'last_heartbeat' => NULL,
    ));

    $resolver->setRequired(array('id', 'username'));
  }

  /**
   * {@inheritdoc}
   */
  public function getRoles()
  {
    return $this->roles;
  }

  /**
   * {@inheritdoc}
   */
  public function getToken()
  {
    return $this->token;
  }

  /**
   * {@inheritdoc}
   */
  public function getSalt()
  {
  }

  /**
   * {@inheritdoc}
   */
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * {@inheritdoc}
   */
  public function eraseCredentials()
  {
  }

  /**
   * {@inheritdoc}
   */
  public function getPassword() {

  }

  /**
   * @return mixed
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @return array
   */
  public function getProperties() {
    return $this->properties;
  }

  /**
   * @return mixed
   */
  public function isLocked() {
    return $this->locked;
  }

  /**
   * Locks the user
   */
  public function lock() {
    $this->locked = true;
  }

  /**
   * Unlocks the user
   */
  public function unlock() {
    $this->locked = false;
  }

  /**
   * @return mixed
   */
  public function getFirstName() {
    return $this->firstName;
  }

  /**
   * @return mixed
   */
  public function getLastName() {
    return $this->lastName;
  }

  /**
   * @return mixed
   */
  public function getEmail() {
    return $this->email;
  }

  /**
   * @return mixed
   */
  public function getFeatures() {
    return $this->features;
  }

  /**
   * @return mixed
   */
  public function getCreated() {
    return $this->created;
  }

  /**
   * @return null
   */
  public function getLastLoggedIn() {
    return $this->last_logged_in;
  }

  /**
   * @return mixed
   */
  public function getLastHeartbeat() {
    return $this->last_heartbeat;
  }

  /**
   * @param mixed $last_heartbeat
   */
  public function setLastHeartbeat($last_heartbeat) {
    $this->last_heartbeat = $last_heartbeat;
  }

  /**
   * @return mixed
   */
  public function getPermissions() {
    return $this->permissions;
  }

}
