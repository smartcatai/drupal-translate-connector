<?php

namespace Smartcat\Drupal\Api;

use \SmartCat\Client\SmartCat;

class Api
{
    /**
     * @var SmartCat
     */
    protected $api;

    /**
     * @var Directory
     */
    protected $directory;

    public $project;

    public function __construct()
    {
        $state = \Drupal::state();

        $login = $state->get('smartcat_api_login');
        $passwd = $state->get('smartcat_api_password');
        $server = $state->get('smartcat_api_server');

        $this->api = new SmartCat($login,$passwd,$server);
        $this->directory = new Directory($this->api);
        $this->project = new Project($this->api);
    }

    public function getAccount()
    {
        return $this->api->getAccountManager()->accountGetAccountInfo();
    }

    public function getLanguages()
    {
        return $this->directory->get('language')->getItems();
    }

    public function getServiceTypes()
    {
        return $this->directory->getItemsAsArray('lspServiceType');
    }

    public function getVendor()
    {
        return $this->directory->getItemsAsArray('vendor');
    }

    public function getProjectStatus()
    {
        return $this->directory->getItemsAsArray('projectStatus');
    }

    public function __call($method, $arguments)
    {
        return $this->api->$method(...$arguments);
    }
}