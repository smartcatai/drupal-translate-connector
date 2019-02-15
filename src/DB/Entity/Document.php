<?php

namespace Drupal\smartcat_translation_manager\DB\Entity;

class Document
{
    const STATUS_CREATED = "created";
    const STATUS_INPROGRESS = "inprogress";
    const STATUS_COMPLETED = "completed";
    const STATUS_CANCELED = "canceled";
    const STATUS_FAILED = "failed";
    const STATUS_DOWNLOADED = "downloaded";

    const STATUSES = [
        self::STATUS_CREATED => "Sent to Smartcat",
        self::STATUS_INPROGRESS => "Translating in Smartcat",
        self::STATUS_COMPLETED => "Completed",
        self::STATUS_CANCELED => "Canceled",
        self::STATUS_FAILED => "Failed",
    ];

    /** @var  integer */
    private $id;

    /** @var  string */
    private $name;

    /** @var  integer */
    private $entityId;

    /** @var  string */
    private $entityTypeId;

    /** @var  string */
    private $sourceLanguage;

    /** @var  string */
    private $targetLanguage;

    /** @var  string */
    private $status;

    /** @var  string */
    private $externalExportId = NULL;

    /** @var  string */
    private $externalProjectId;

    /** @var  string */
    private $externalDocumentId;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Document
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
     * @return Document
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
     * @return Document
     */
    public function setEntityId($entityId) {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityTypeId() {
        return $this->entityTypeId;
    }

    /**
     * @param string $entityTypeId
     *
     * @return Document
     */
    public function setEntityTypeId($entityTypeId) {
        $this->entityTypeId = $entityTypeId;

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
     * @return Document
     */
    public function setExternalProjectId($externalProjectId) {
        $this->externalProjectId = $externalProjectId;

        return $this;
    }

    /**
     * @return string
     */
    public function getExternalDocumentId() {
        return $this->externalDocumentId;
    }

    /**
     * @param string $externaldocumentId
     *
     * @return Document
     */
    public function setExternalDocumentId($externalDocumentId) {
        $this->externalDocumentId = $externalDocumentId;

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
     * @return Document
     */
    public function setSourceLanguage($sourceLanguage) {
        $this->sourceLanguage = $sourceLanguage;

        return $this;
    }

    /**
     * @return string
     */
    public function getTargetLanguage() {
        return $this->targetLanguage;
    }

    /**
     * @param string $targetLanguages
     *
     * @return Document
     */
    public function setTargetLanguage($targetLanguage) {
        $this->targetLanguage = $targetLanguage;

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
     * @return Document
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getExternalExportId() {
        return $this->externalExportId;
    }

    /**
     * @param string $externalExportId
     *
     * @return Document
     */
    public function setExternalExportId($externalExportId) {
        $this->externalExportId = $externalExportId;

        return $this;
    }

}