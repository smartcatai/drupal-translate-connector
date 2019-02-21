<?php

namespace Drupal\smartcat_translation_manager\Helper;

use Drupal\Core\Link;
use Drupal\Core\Url;

class ApiHelper
{
    public static function filterChars($s) {
        return mb_substr(str_replace(['*', '|', '\\', ':', '"', '<', '>', '?', '/'], '_', $s), 0, 94);
    }

    public static function getProjectName($project){
        $name = $project->getName();
        if($project->getExternalProjectId()){
            $projectUrl = self::getProjectUrl($project);
            $name = Link::fromTextAndUrl($project->getName(),$projectUrl)->toString();
        }
        return $name;
    }

    public static function getProjectUrl($project)
    {
        return Url::fromUri("https://smartcat.ai/projects/{$project->getExternalProjectId()}");
    }

    public static function getProjectUrlBydocument($document)
    {
        return Url::fromUri("https://smartcat.ai/projects/{$document->getExternalProjectId()}");
    }

    public static function getDocumentLink($document_id){
        $ids = explode('_',$document_id);
        $state = \Drupal::state();
        return "https://{$state->get('smartcat_api_server')}/editor?DocumentId={$ids[0]}&LanguageId={$ids[1]}";
    }
}