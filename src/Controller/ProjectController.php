<?php

namespace Smartcat\Drupal\Controller;

use Drupal\Component\Render\FormattableMarkup;  
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Smartcat\Drupal\DB\Entity\Project;
use Smartcat\Drupal\DB\Repository\ProjectRepository;
use Smartcat\Drupal\Helper\ApiHelper;
use Smartcat\Drupal\Helper\FileHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Cache\Cache;
class ProjectController extends ControllerBase
{
    /**
     * @var \Smartcat\Drupal\Api\Api
     */
    protected $api;
    protected $projectRepository;

    public function __construct()
    {
        $this->api = new \Smartcat\Drupal\Api\Api();
        $this->projectRepository = new ProjectRepository();
    }

    public function content() {
        $table = [
            '#type' => 'table',
            '#title' => 'Projects',
            '#header' =>[
                'Project name',
                'Content',
                'Translate to',
                'Status',
                'Operations',
            ],
            '#rows' => [
            ]
        ];
        $criteria = [];
        $project_id = \Drupal::request()->query->get('project_id');
        if($project_id){
            $criteria['id'] = $project_id;
        }
        $projects = $this->projectRepository->getBy($criteria);
        $entityManager = \Drupal::entityTypeManager();

        if(!empty($projects)){
            foreach($projects as $i=>$project){
                $entity = $entityManager
                    ->getStorage($project->getEntityTypeId())
                    ->load($project->getEntityId());

                if($entity){
                    $table['#rows'][$i] = [
                        ApiHelper::getProjectName($project),
                        $entity->label(),
                        implode('|',$project->getTargetLanguages()),
                        $project->getStatus(),
                        [
                            'data' => [
                                '#type' => 'form',
                                '#action' => Url::fromRoute('smartcat_translation_manager.project.delete',['id'=>$project->getId()])->toString(),
                                'submit' => [
                                    '#type'=>'submit',
                                    '#value'=>'Delete',
                                ],
                            ],
                        ],
                    ];
                }else{
                    $table['#rows'][$i] = [
                        $project->getName(),
                        'Not exists',
                        implode('|',$project->getTargetLanguages()),
                        $project->getStatus(),
                        [
                            'data' => [
                                '#type' => 'form',
                                '#action' => Url::fromRoute('smartcat_translation_manager.project.delete',['id'=>$project->getId()])->toString(),
                                'submit' => [
                                    '#type'=>'submit',
                                    '#value'=>'Delete',
                                ],
                            ],
                        ],
                    ];
                }
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

    public function delete($id)
    {
        $this->projectRepository->delete($id);
        return new RedirectResponse(Url::fromRoute('smartcat_translation_manager.project')->toString());
    }

    public function add()
    {
        $ProjectService = \Drupal::service('smartcat_translation_manager.service.project');
        $entityManager = \Drupal::entityTypeManager();

        $type_id = \Drupal::request()->query->get('type_id');
        $entity_id = \Drupal::request()->query->get('entity_id');
        $lang = \Drupal::request()->query->get('lang');
        $lang = !is_array($lang) ? [$lang] : $lang;

        $entity = $entityManager
            ->getStorage($type_id)
            ->load($entity_id);

        if(!$entity){
            throw new NotFoundHttpException("Entity $type_id $entity_id not found");
        }

        try{
            $project_id = $ProjectService->createProject($entity, $lang);
        }catch(\Exception $e){
            throw new \HttpException(500, $e->getMessage());
        }

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