<?php

namespace UserAppSymfony;

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


  public function __construct($id, $username, $token, $firstName = null, $lastName = null, $email = null, $roles = array(), $properties = array(), $features = array(), $permissions = array(), $created = null, $locked = false)
  {
    if (empty($username)) {
      throw new \InvalidArgumentException('The username cannot be empty.');
    }

    if (empty($id)) {
      throw new \InvalidArgumentException('The id cannot be empty.');
    }

    $this->id = $id;
    $this->username = $username;
    $this->token = $token;
    $this->firstName = $firstName;
    $this->lastName = $lastName;
    $this->email = $email;
    $this->roles = $roles;
    $this->properties = $properties;
    $this->features = $features;
    $this->permissions = $permissions;
    $this->created = $created;
    $this->locked = $locked;
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
}