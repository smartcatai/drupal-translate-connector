<?php

namespace Smartcat\Drupal;

use Smartcat\Drupal\DB\Entity\Project;
use Smartcat\Drupal\DB\Repository\ProjectRepository;

class CronHandler
{
    const KEY_LAST_RUN = 'smartcat_cron.last_run';
    const CRON_PERIOD = 30;

    /**
     * @var \Smartcat\Drupal\Api\Api
     */
    protected $api;

    /**
     * @var ProjectRepository
     */
    protected $projectRepository;

    public static function create()
    {
        $last_run = \Drupal::state()->get(self::KEY_LAST_RUN, 0);

        if ((REQUEST_TIME - $last_run) > self::CRON_PERIOD) {
            \Drupal::state()->set(self::KEY_LAST_RUN, REQUEST_TIME);
            return new static();
        }
        return;
    }

    public function __construct(){
        $this->api = new \Smartcat\Drupal\Api\Api();
        $this->projectRepository = new ProjectRepository();
    } 

    public function run()
    {
        return true;
    }

    public function buildStatistic()
    {
        $projects = $this->projectRepository->getBy(['status'=>Project::STATUS_NEW]);
        if(!empty($projects)){
            foreach($projects as $i=>$project){
                $scProject = $this->api->buildStatistic($project->getExtenalProjectId());
                if($project->setStatus()===$scProject->getStatus()){
                    $project->setStatus($scProject->getStatus());
                    $this->projectRepository->update($project);
                }
            }
        }
    }
}