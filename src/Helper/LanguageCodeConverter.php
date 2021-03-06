<?php

namespace Drupal\smartcat_translation_manager\Helper;

/**
 * Class converte langcode from Drupal to Smartcat and vice versa
 */
class LanguageCodeConverter {

  /**
   * @param string $drupalLangCode
   * @return string
   */
  public static function convertDrupalToSmartcat($drupalLangCode) {
    if (isset(self::getLanguageCodes()[$drupalLangCode])) {
      return self::getLanguageCodes()[$drupalLangCode];
    }
    return $drupalLangCode;
  }

  /**
   * @param string $smartcatLangCode
   * @return string
   */
  public static function convertSmartcatToDrupal($smartcatLangCode) {
    $codes = array_flip(self::getLanguageCodes());
    if (isset($codes[$smartcatLangCode])) {
      return $codes[$smartcatLangCode];
    }
    return $smartcatLangCode;
  }

  /**
   * @return array
   */
  protected static function getLanguageCodes() {
    return [
      'ab' => 'ab',
      'aa' => 'aa',
      'af' => 'af',
      'am' => 'am',
      'ar' => 'ar',
      'as' => 'as',
      'av' => 'av',
      'az' => 'az',
      'bm' => 'bm',
      'ba' => 'ba',
      'be' => 'be',
      'bn' => 'bn',
      'bh' => 'bh',
      'bg' => 'bg',
      'bo' => 'bo',
      'bs' => 'bs',
      'ca' => 'ca',
      'cs' => 'cs',
      'da' => 'da',
      'de-ch' => 'de-CH',
      'de' => 'de-DE',
      'el' => 'el',
      'en-gb' => 'en-GB',
      'en' => 'en-US',
      'eo' => 'eo',
      'es' => 'es',
      'et' => 'et',
      'eu' => 'eu',
      'fi' => 'fi',
      'fr' => 'fr-FR',
      'ga' => 'ga',
      'gl' => 'gl',
      'gn' => 'gn',
      'gu' => 'gu',
      'he' => 'he',
      'hi' => 'hi',
      'hr' => 'hr',
      'hu' => 'hu',
      'hy' => 'hy',
      'id' => 'id',
      'is' => 'is',
      'it' => 'it',
      'ja' => 'ja',
      'jv' => 'jv',
      'ka' => 'ka',
      'kk' => 'kk',
      'km' => 'km',
      'kn' => 'kn',
      'ko' => 'ko',
      'ku' => 'ku',
      'ky' => 'ky',
      'lb' => 'lb',
      'lo' => 'lo',
      'lt' => 'lt',
      'lv' => 'lv',
      'mg' => 'mg',
      'mk' => 'mk',
      'ml' => 'ml',
      'mn' => 'mn',
      'mr' => 'mr',
      'ms' => 'ms',
      'my' => 'my',
      'nb' => 'nb',
      'ne' => 'ne',
      'nl' => 'nl',
      'nn' => 'nn',
      'os' => 'os',
      'pa' => 'pa',
      'pl' => 'pl',
      'ps' => 'ps',
      'pt-br' => 'pt-BR',
      'pt-pt' => 'pt-PT',
      'pt' => 'pt-PT',
      'ro' => 'ro',
      'ru' => 'ru',
      'sa' => 'sa',
      'sah' => 'sah',
      'si' => 'si',
      'sk' => 'sk',
      'sl' => 'sl',
      'so' => 'so',
      'sq' => 'sq',
      'sr' => 'sr-Latn',
      'sv' => 'sv',
      'sw' => 'sw',
      'ta' => 'ta',
      'te' => 'te',
      'tg' => 'tg',
      'th' => 'th',
      'ti' => 'ti',
      'tk' => 'tk',
      'tl' => 'tl',
      'tr' => 'tr',
      'tt' => 'tt',
      'ug' => 'ug',
      'uk' => 'uk',
      'ur' => 'ur',
      'uz' => 'uz-Latn',
      'vi' => 'vi',
      'zh-hans' => 'zh-Hans',
      'zh-hant' => 'zh-Hant-HK',
      'ak' => 'ak',
      'dz' => 'bcc',
      'ceb' => 'ceb',
      'co' => 'it',
      'fa' => 'fa',
      'ff' => 'ff',
      'gsw-berne' => 'de-CH',
      'haz' => 'haz',
      'kab' => 'kab',
      'rw' => 'rw',
      'li' => 'li',
      'ln' => 'ln',
      'me' => 'sr-Latn',
      'oc' => 'oc',
      'or' => 'or',
      'rhg' => 'rhg-Latn',
      'rue' => 'uk',
      'sd' => 'sd',
      'sc' => 'sc',
      'su' => 'su',
      'szl' => 'pl',
      'yo' => 'yo',
      'wo' => 'wo',
      'ht' => 'ht',
      'zu' => 'zu',
      'yi' => 'yi',
      'rn' => 'rn',
      'kv' => 'kv',
      'kw' => 'kw',
      'la' => 'la',
      'sm' => 'sm',
      'sg' => 'sg',
      'tn' => 'tn',
      'to' => 'to',
      'fil' => 'fil',
      'ce' => 'ce',
      'cv' => 'cv',
      'sn' => 'sn',
    ];
  }

}
