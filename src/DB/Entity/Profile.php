<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 17.07.2017
 * Time: 14:11
 */

namespace SmartCAT\Drupal\DB\Entity;


class Profile {

  /** @var  integer */
  private $id;

  /** @var  string */
  private $name;

  /** @var  string */
  private $sourceLanguage;

  /** @var  string[] */
  private $targetLanguages;

  /** @var  string */
  private $vendor;

  /** @var  string[] */
  private $workflowStages;

  /** @var  string */
  private $entityType;

  /** @var bool */
  private $autoPublish;

  /** @var bool */
  private $autoTranslate;

  /**
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param int $id
   *
   * @return Profile
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
   * @return Profile
   */
  public function setName($name) {
    $this->name = $name;

    return $this;
  }

  /**
   * @return string
   */
  public function getSourceLanguage() {
    return $this->sourceLanguage;
  }

  /**
   * @param string $sourceLanguage
   *
   * @return Profile
   */
  public function setSourceLanguage($sourceLanguage) {
    $this->sourceLanguage = $sourceLanguage;

    return $this;
  }

  /**
   * @return string[]
   */
  public function getTargetLanguages() {
    return $this->targetLanguages;
  }

  /**
   * @param string[] $targetLanguages
   *
   * @return Profile
   */
  public function setTargetLanguages($targetLanguages) {
    $this->targetLanguages = $targetLanguages;

    return $this;
  }

    /**
   * @return string
   */
  public function getVendor() {
    return $this->vendor;
  }

  /**
   * @param string $vendor
   *
   * @return Profile
   */
  public function setVendor($vendor) {
    $this->vendor = $vendor;

    return $this;
  }

  /**
   * @return string[]
   */
  public function getWorkflowStages() {
    return $this->workflowStages;
  }

  /**
   * @param string[] $workflowStages
   *
   * @return Profile
   */
  public function setWorkflowStages($workflowStages) {
    $this->workflowStages = $workflowStages;

    return $this;
  }

  /**
   * @return string
   */
  public function getEntityType() {
    return $this->entityType;
  }

  /**
   * @param string $entityType
   *
   * @return Profile
   */
  public function setEntityType(string $entityType) {
    $this->entityType = $entityType;
    return $this;
  }

  /**
   * @return int
   */
  public function getAutoPublish() {
    return $this->autoPublish;
  }

  /**
   * @param int $autoPublish
   *
   * @return Profile
   */
  public function setAutoPublish($autoPublish) {
    $this->autoPublish = $autoPublish;

    return $this;
  }

    /**
   * @return int
   */
  public function getAutoTranslate() {
    return $this->autoTranslate;
  }

  /**
   * @param int $autoTranslate
   *
   * @return Profile
   */
  public function setAutoTranslate($autoTranslate) {
    $this->autoTranslate = $autoTranslate;

    return $this;
  }
}