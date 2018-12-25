<?php

namespace Smartcat\Drupal\Controller;

use Drupal\Component\Render\FormattableMarkup;  
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
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
            $api = new \Smartcat\Drupal\Api\Api();
            $vendors = $api->getVendor();
            foreach($profiles as $i=>$profile){
                $table['#rows'][$i] = [
                    ['data'=>new FormattableMarkup('<a href=":link">@name</a>',
                        [':link' => '/admin/smartcat/profile/edit?profile_id='.$profile->getId(), 
                        '@name' => $profile->getName()]
                    )],
                    $vendors[$profile->getVendor()],
                    $profile->getSourceLanguage(),
                    implode('|',$profile->getTargetLanguages()),
                ];
            }
        }

        $url = Url::fromRoute('smartcat_translation_manager.profile.edit');
        $link = Link::fromTextAndUrl('Add profile', $url);
        $link = $link->toRenderable();
        $link['#attributes'] = ['class'=>'button button-action button--primary button--small'];

        return [
            '#type' => 'page',
            'header' => ['#markup'=>'<h1>Profiles list</h1>'],
            'content' => [
                ['#markup'=>'<br>'],
                $link,
                ['#markup'=>'<br><br>'],
                $table,
            ],
        ];
    }
}