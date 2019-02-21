<?php

namespace Drupal\smartcat_translation_manager\Controller;

use Drupal\Component\Render\FormattableMarkup;  
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\smartcat_translation_manager\DB\Entity\Project;
use Drupal\smartcat_translation_manager\DB\Repository\ProjectRepository;
use Drupal\smartcat_translation_manager\Helper\ApiHelper;
use Drupal\smartcat_translation_manager\Helper\FileHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Cache\Cache;
class ProjectController extends ControllerBase
{
    /**
     * @var \Drupal\smartcat_translation_manager\Api\Api
     */
    protected $api;
    protected $projectRepository;
    protected $tempStore;

    public function __construct()
    {
        $this->api = new \Drupal\smartcat_translation_manager\Api\Api();
        $this->projectRepository = new ProjectRepository();
        $this->tempStore = \Drupal::service('tempstore.private')->get('entity_translate_multiple_confirm');
    }

    public function content() {
        $table = [
            '#type' => 'table',
            '#title' => 'Projects',
            '#header' =>[
                'Project name',
             //   'Content',
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
                // $entity = $entityManager
                //     ->getStorage($project->getEntityTypeId())
                //     ->load($project->getEntityId());

                //if($entity){
                    $table['#rows'][$i] = [
                        ApiHelper::getProjectName($project),
                        //$entity->label(),
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
                // }else{
                //     $table['#rows'][$i] = [
                //         $project->getName(),
                //         //'Not exists',
                //         implode('|',$project->getTargetLanguages()),
                //         $project->getStatus(),
                //         [
                //             'data' => [
                //                 '#type' => 'form',
                //                 '#action' => Url::fromRoute('smartcat_translation_manager.project.delete',['id'=>$project->getId()])->toString(),
                //                 'submit' => [
                //                     '#type'=>'submit',
                //                     '#value'=>'Delete',
                //                 ],
                //             ],
                //         ],
                //     ];
                // }
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

        $selection = [];
    
        $langcode = $entity->language()->getId();
        $selection[$entity->id()][$langcode] = $langcode;

        $this->tempStore->set(\Drupal::service('current_user')->id() . ':' . $type_id, $selection);
        $previousUrl = \Drupal::request()->server->get('HTTP_REFERER');
        $base_url = Request::createFromGlobals()->getSchemeAndHttpHost();
        // Getting the alias or the relative path.
        $destination = substr($previousUrl, strlen($base_url));
        //die;
        // try{
        //     $project_id = $ProjectService
        //         ->addEntityToTranslete($entity, $lang)
        //         ->sendProjectWithDocuments();
        // }catch(\Exception $e){
        //     throw new HttpException(500, $e->getMessage());
        // }

        // return new JsonResponse([
        //     'data'=>'yes',
        //     'lang' => $lang,
        //     'entity_id' => $entity_id,
        //     'name'=>$entity->label(),
        //     'project' =>$project_id,
        //     'entity_body' => $entity->body->value,
        // ]);

        return new RedirectResponse(
            Url::fromRoute('smartcat_translation_manager.settings_more', 
                ['entity_type_id'=>$type_id ],
                ['query'=>['destination'=>$destination]]
            )->toString()
        );
    }
}