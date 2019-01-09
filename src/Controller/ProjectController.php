<?php

namespace Smartcat\Drupal\Controller;

use Drupal\Component\Render\FormattableMarkup;  
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Smartcat\Drupal\DB\Entity\Project;
use Smartcat\Drupal\DB\Repository\ProfileRepository;
use Smartcat\Drupal\DB\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
class ProjectController extends ControllerBase
{
    public function content() {
        $table = [
            '#type' => 'table',
            '#title' => 'Профили',
            '#header' =>[
                'Профиль',
                'Элемент',
                'Перевод',
                'Название проекта',
                'Статус',
            ],
            '#rows' => [
            ]
        ];
        $projects = (new ProjectRepository())->getBy();
        $entityManager = \Drupal::entityTypeManager();
        $profileRepository = new ProfileRepository();

        if(!empty($projects)){
            foreach($projects as $i=>$project){
                $prifile= $profileRepository->getOneBy(['id'=>$project->getProfileId]);
                $entity = $entityManager
                    ->getStorage($profile->getEntityType())
                    ->load($project->getEntityId());
                $table['#rows'][$i] = [
                    $entity->label(),
                    $profile->getName(),
                    $project->getName(),
                    $project->getStatus(),
                ];
            }
        }

        return [
            '#type' => 'page',
            'header' => ['#markup'=>'<h1>Projects list</h1>'],
            'content' => [
                ['#markup'=>'<br>'],
                $table,
            ],
        ];
    }

    public function add()
    {
        $entityManager = \Drupal::entityTypeManager();
        $type_id = \Drupal::request()->query->get('type_id');
        $entity_id = \Drupal::request()->query->get('entity_id');
        $lang = \Drupal::request()->query->get('lang');
        $entity = $entityManager
            ->getStorage($type_id)
            ->load($entity_id);
        $profile = (new ProfileRepository())->getOneBy(['entityType'=>$type_id]);

        
        $project = (new Project())
            ->setName($entity->label())
            ->setEntityId($entity_id)
            ->setProfileId($profile ? $profile->getId() : 0)
            ->setStatus(Project::STATUS_NEW);
    
        
        $project_id = (new ProjectRepository())->add($project);
        
        return new JsonResponse([
            'data'=>'yes',
            'lang' => $lang,
            'entity_id' => $entity_id,
            'name'=>$entity->label(),
            'project' =>$project_id,
        ]);
    }
}