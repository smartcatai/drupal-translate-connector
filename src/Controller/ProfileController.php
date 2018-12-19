<?php

namespace Smartcat\Drupal\Controller;

use Drupal\Component\Render\FormattableMarkup;  
use Drupal\Core\Controller\ControllerBase;
use Smartcat\Drupal\DB\Repository\ProfileRepository;
class ProfileController extends ControllerBase
{
    public function content() {
        $table = [
            '#type' => 'table',
            '#title' => 'Профили',
            '#header' =>[
                $this->t('Name'),
                'Вендор',
                'Язык оригинала',
                'Языки переводов',
            ],
            '#rows' => [
            ]
        ];
        $profiles = (new ProfileRepository())->getBy();

        if(!empty($profiles)){
            foreach($profiles as $i=>$profile){
                $table['#rows'][$i] = [
                    ['data'=>new FormattableMarkup('<a href=":link">@name</a>',
                        [':link' => '/admin/smartcat/profile/edit?profile_id='.$profile->getId(), 
                        '@name' => $profile->getName()]
                    )],
                    $profile->getVendor(),
                    $profile->getSourceLanguage(),
                    implode('|',$profile->getTargetLanguages()),
                ];
            }
        }
        return [
            '#type' => 'page',
            '#title' => 'Профили переводов',
            'content' => [
                $table,
            ],
        ];
    }
}