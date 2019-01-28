<?php

namespace Smartcat\Drupal\Controller;

use Drupal\Component\Render\FormattableMarkup;  
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Smartcat\Drupal\DB\Entity\Project;
use Smartcat\Drupal\DB\Repository\ProjectRepository;
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
            '#title' => 'Проекты',
            '#header' =>[
                'Название проекта',
                'Элемент',
                'Перевод',
                'Статус',
                'Операции',
            ],
            '#rows' => [
            ]
        ];
        $projects = $this->projectRepository->getBy();
        $entityManager = \Drupal::entityTypeManager();

        if(!empty($projects)){
            foreach($projects as $i=>$project){
                $entity = $entityManager
                    ->getStorage($project->getEntityTypeId())
                    ->load($project->getEntityId());
                if($entity){
                    $table['#rows'][$i] = [
                        $project->getName(),
                        $entity->label(),
                        $project->getTargetLanguages(),//implode('|',$project->getTargetLanguages()),
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
        $entityManager = \Drupal::entityTypeManager();
        $type_id = \Drupal::request()->query->get('type_id');
        $bundle = \Drupal::request()->query->get('type');
        $entity_id = \Drupal::request()->query->get('entity_id');
        $lang = \Drupal::request()->query->get('lang');
        $lang = !is_array($lang) ? [$lang] : $lang;
        $entity = $entityManager
            ->getStorage($type_id)
            ->load($entity_id);

        if(!$entity){
            throw new NotFoundHttpException("Entity $type_id $entity_id not found");
        }

        $projectManager = $this->api->getProjectManager();

        $project = (new Project())
            ->setName($entity->label())
            ->setEntityId($entity_id)
            ->setEntityTypeId($type_id)
            ->setSourceLanguage($entity->language()->getId())
            ->setTargetLanguages($lang)
            ->setStatus(Project::STATUS_NEW);

        try{
            $scProject = $this->api->createProject($project);
        }catch(\Exception $e){
            throw new \HttpException(500, $e->getMessage());
        }

        $project->setExternalProjectId($scProject->getId());
        $project->setName($scProject->getName());
        $project_id = $this->projectRepository->add($project);

        $fileHelper = new FileHelper($entity);
        $dest = $fileHelper->createFileByEntity(['title','body','comment']);

        $documentModel = $this->api->project->createDocumentFromFile($dest, 'TRANSLATED-' . $type_id .'.'. $bundle .'.'. $entity_id .'.html');

        $documents = $projectManager->projectAddDocument([
            'documentModel' => [$documentModel],
            'projectId' => $scProject->getId(),
        ]);

        $vendor = \Drupal::state()->get('smartcat_api_vendor', '0');
        if($vendor !=='0'){
            $projectChanges = $this->api->project->createVendorChange($vendor);
            $projectChanges
                ->setName($scProject->getName())
                ->setDescription($scProject->getDescription())
                ->setDeadline($scProject->getDeadline());

            $projectManager->projectUpdateProject($scProject->getId(), $projectChanges);
        }

        return new JsonResponse([
            'data'=>'yes',
            'lang' => $lang,
            'entity_id' => $entity_id,
            'name'=>$entity->label(),
            'project' =>$project_id,
            'entity_body' => $entity->body->value,
            'dest' => $dest,
        ]);
    }
}