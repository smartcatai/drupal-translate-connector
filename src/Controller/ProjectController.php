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
            '#title' => 'Проекты',
            '#header' =>[
                'Название проекта',
                'Элемент',
                'Перевод',
                'Статус',
            ],
            '#rows' => [
            ]
        ];
        $projects = (new ProjectRepository())->getBy();
        $entityManager = \Drupal::entityTypeManager();

        if(!empty($projects)){
            foreach($projects as $i=>$project){
                $entity = $entityManager
                    ->getStorage($project->getEntityTypeId())
                    ->load($project->getEntityId());
                $table['#rows'][$i] = [
                    $project->getName(),
                    $entity->label(),
                    implode('|',$project->getTargetLanguages()),
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
        $bundle = \Drupal::request()->query->get('type');
        $entity_id = \Drupal::request()->query->get('entity_id');
        $lang = \Drupal::request()->query->get('lang');
        $entity = $entityManager
            ->getStorage($type_id)
            ->load($entity_id);

        // $project = (new Project())
        //     ->setName($entity->label())
        //     ->setEntityId($entity_id)
        //     ->setEntityTypeId($type_id)
        //     ->setTargetLanguages([$lang])
        //     ->setStatus(Project::STATUS_NEW);

        // $project_id = (new ProjectRepository())->add($project);
        // $config = $entityManager
        //     ->getStorage('language_content_settings')
        //     ->load($type_id . '.' . $bundle);

        // $configType = '';
        // if($config !== null){
        //     $configType = $config->getLanguageAlterable();
        // }
        var_dump(array_keys($entity->getProperties()));

        return new JsonResponse([
            'data'=>'yes',
            'lang' => $lang,
            'entity_id' => $entity_id,
            'name'=>$entity->label(),
            'project' =>$project_id,
            'entity_body' => $entity->body->value,
        ]);
    }
}