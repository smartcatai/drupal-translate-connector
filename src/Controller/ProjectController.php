<?php

namespace Smartcat\Drupal\Controller;

use Drupal\Component\Render\FormattableMarkup;  
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Smartcat\Drupal\DB\Repository\ProfileRepository;
use Smartcat\Drupal\DB\Repository\ProjectRepository;
class ProfileController extends ControllerBase
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
                    $entity->getTitle(),
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
}