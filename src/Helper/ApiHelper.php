<?php

namespace Smartcat\Drupal\Helper;

class ApiHelper
{
    public static function filterChars($s) {
        return substr(str_replace(['*', '|', '\\', ':', '"', '<', '>', '?', '/'], '_', $s), 0, 100);
    }

    public static function getDocumentLink($document_id){
        $ids = explode('_',$document_id);
        $state = \Drupal::state();
        return "https://{$state->get('smartcat_api_server')}/editor?DocumentId={$ids[0]}&LanguageId={$ids[1]}";
    }
}