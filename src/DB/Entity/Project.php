<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 17.07.2017
 * Time: 14:11
 */

namespace SmartCAT\Drupal\DB\Entity;


class Project
{

  /** @var  integer */
  private $id;

  /** @var  string */
  private $name;

  /** @var  integer */
  private $entityId;

  /** @var  integer */
  private $profileId;

  /** @var  string */
  private $status;

  /** @var  string */
  private $externalProjectId = NULL;


  /**
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param int $id
   *
   * @return Project
   */
  public function setId($id) {
    $this->id = $id;

    return $this;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @param string $name
   *
   * @return Project
   */
  public function setName($name) {
    $this->name = $name;

    return $this;
  }

  /**
   * @return int
   */
  public function getEntityId() {
    return $this->entityId;
  }

  /**
   * @param int $entityId
   *
   * @return Project
   */
  public function setEntityId($entityId) {
    $this->entityId = $entityId;

    return $this;
  }

    /**
   * @return int
   */
  public function getProfileId() {
    return $this->profileId;
  }

  /**
   * @param int $profileId
   *
   * @return Project
   */
  public function setProfileId($profileId) {
    $this->profileId = $profileId;

    return $this;
  }

  /**
   * @return string
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * @param string $status
   *
   * @return Project
   */
  public function setStatus($status) {
    $this->status = $status;

    return $this;
  }

  /**
   * @return string
   */
  public function getExternalProjectId() {
    return $this->externalProjectId;
  }

  /**
   * @param string $externalProjectId
   *
   * @return Project
   */
  public function setExternalProjectId($externalProjectId) {
    $this->externalProjectId = $externalProjectId;

    return $this;
  }

}