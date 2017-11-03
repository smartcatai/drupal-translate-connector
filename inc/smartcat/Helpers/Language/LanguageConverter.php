<?php

namespace SmartCAT\Drupal\Helpers\Language;

use SmartCAT\Drupal\Helpers\Language\Exceptions\LanguageNotFoundException;

//TODO: возможно вынести отдельно релэйшены длы Drupal и SC по разным классам, данный класс оставить в качестве интерфейсного
final class LanguageConverter {
	protected $drupal_to_sc_relations;
	protected $sc_to_drupal_relations;

	//весь сырбор из-за требования обратной конвертации (из SC в Drupal)
	protected function add_relation($drupal_code, $sc_code, $drupal_name, $sc_name = null ) {
		//использую объект для передачи по ссылке в два места (теоретически, сэкономит память)
		$language = new LanguageEntity( $drupal_code, $sc_code, $drupal_name, $sc_name );

		//две ассоциации для быстрой выборки, возможно от второй можно будет отказаться
		$this->drupal_to_sc_relations[ $drupal_code ]   = $language; // 1 к 1
		$this->sc_to_drupal_relations[ $sc_code ][] = $language; // 1 ко многим
	}

	protected function init() {
		//отдельным методом, чтоб можно было подменить при необходимости
    $this->add_relation( 'ab', 'ab', t('Abkhazian', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'aa', 'aa', t('Afar', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'af', 'af', t('Afrikaans', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'am', 'am', t('Amharic', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ar', 'ar', t('Arabic', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'as', 'as', t('Assamese', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'av', 'av', t('Avar', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'az', 'az', t('Azerbaijani', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'bm', 'bm', t('Bamanankan', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ba', 'ba', t('Bashkir', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'be', 'be', t('Belarusian', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'bn', 'bn', t('Bengali', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'bh', 'bh', t('Bihari', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'bg', 'bg', t('Bulgarian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'bo', 'bo', t('Tibetan', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'bs', 'bs', t('Bosnian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ca', 'ca', t('Catalan', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'cs', 'cs', t('Czech', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'da', 'da', t('Danish', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'de-ch', 'de-CH', t('German (Switzerland)', [], ['context' => 'translation_connectors'])); //Нет языка
		$this->add_relation( 'de', 'de-DE', t('German', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'el', 'el', t('Greek', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'en-gb', 'en-GB', t('English (UK)', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'en', 'en-US', t('English', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'eo', 'eo', t('Esperanto', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'es', 'es', t('Spanish', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'et', 'et', t('Estonian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'eu', 'eu', t('Basque', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'fi', 'fi', t('Finnish', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'fr', 'fr-FR', t('French (France)', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ga', 'ga', t('Irish', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'gl', 'gl', t('Galician', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'gn', 'gn', t('Guaraní', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'gu', 'gu', t('Gujarati', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'he', 'he', t('Hebrew', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'hi', 'hi', t('Hindi', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'hr', 'hr', t('Croatian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'hu', 'hu', t('Hungarian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'hy', 'hy', t('Armenian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'id', 'id', t('Indonesian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'is', 'is', t('Icelandic', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'it', 'it', t('Italian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ja', 'ja', t('Japanese', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'jv', 'jv', t('Javanese', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ka', 'ka', t('Georgian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'kk', 'kk', t('Kazakh', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'km', 'km', t('Khmer', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'kn', 'kn', t('Kannada', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ko', 'ko', t('Korean', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ku', 'ku', t('Kurdish (Sorani)', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ky', 'ky', t('Kirghiz', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'lb', 'lb', t('Luxembourgish', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'lo', 'lo', t('Lao', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'lt', 'lt', t('Lithuanian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'lv', 'lv', t('Latvian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'mg', 'mg', t('Malagasy', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'mk', 'mk', t('Macedonian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ml', 'ml', t('Malayalam', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'mn', 'mn', t('Mongolian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'mr', 'mr', t('Marathi', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ms', 'ms', t('Malay', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'my', 'my', t('Myanmar (Burmese)', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'nb', 'nb', t('Norwegian (Bokmål)', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ne', 'ne', t('Nepali', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'nl', 'nl', t('Dutch', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'nn', 'nn', t('Norwegian (Nynorsk)', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'os', 'os', t('Ossetic', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'pa', 'pa', t('Punjabi', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'pl', 'pl', t('Polish', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ps', 'ps', t('Pashto', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'pt-br', 'pt-BR', t('Portuguese (Brazil)', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'pt-pt', 'pt-PT', t('Portuguese (Portugal)', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'pt', 'pt-PT', t('Portuguese (Portugal)', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ro', 'ro', t('Romanian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ru', 'ru', t('Russian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'sa', 'sa', t('Sanskrit', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'sah', 'sah', t('Sakha', [], ['context' => 'translation_connectors'])); //Нет языка
		$this->add_relation( 'si', 'si', t('Sinhala', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'sk', 'sk', t('Slovak', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'sl', 'sl', t('Slovenian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'so', 'so', t('Somali', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'sq', 'sq', t('Albanian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'sr', 'sr-Latn', t('Serbian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'sv', 'sv', t('Swedish', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'sw', 'sw', t('Swahili', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ta', 'ta', t('Tamil', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'te', 'te', t('Telugu', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'tg', 'tg', t('Tajik', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'th', 'th', t('Thai', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ti', 'ti', t('Tigrinya', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'tk', 'tk', t('Turkmen', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'tl', 'tl', t('Tagalog', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'tr', 'tr', t('Turkish', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'tt', 'tt', t('Tatar', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ug', 'ug', t('Uighur', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'uk', 'uk', t('Ukrainian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ur', 'ur', t('Urdu', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'uz', 'uz-Latn', t('Uzbek', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'vi', 'vi', t('Vietnamese', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'zh-hans', 'zh-Hans', t('Chinese (China)', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'zh-hant', 'zh-Hant-HK', t('Chinese (Hong Kong)', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ak', 'ak', t('Akan', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'dz', 'bcc', t('Balochi Southern', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ceb', 'ceb', t('Cebuano', [], ['context' => 'translation_connectors'])); //Нет языка
		$this->add_relation( 'co', 'it', t('Corsican', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'fa', 'fa', t('Persian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'ff', 'ff', t('Fulah', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'gsw-berne', 'de-CH', t('Swiss German', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'haz', 'haz', t('Hazaragi', [], ['context' => 'translation_connectors'])); //Нет языка
		$this->add_relation( 'kab', 'kab', t('Kabyle', [], ['context' => 'translation_connectors'])); //Нет языка
		$this->add_relation( 'rw', 'rw', t('Kinyarwanda', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'li', 'li', t('Limburgish', [], ['context' => 'translation_connectors'])); //Нет языка
		$this->add_relation( 'ln', 'ln', t('Lingala', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'me', 'sr-Latn', t('Montenegrin', [], ['context' => 'translation_connectors'])); //Нет языка
		$this->add_relation( 'oc', 'oc', t('Occitan', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'or', 'or', t('Oriya', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'rhg', 'rhg-Latn', t('Rohingya', [], ['context' => 'translation_connectors']));  //Нет языка
		$this->add_relation( 'rue', 'uk', t('Rusyn', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'sd', 'sd', t('Sindhi', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'sc', 'sc', t('Sardinian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'su', 'su', t('Sundanese', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'szl', 'pl', t('Silesian', [], ['context' => 'translation_connectors']));
		$this->add_relation( 'yo', 'yo', t('Yoruba', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'wo', 'wo', t('Wolof', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'ht', 'ht', t('Haitian Creole', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'zu', 'zu', t('Zulu', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'yi', 'yi', t('Yiddish', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'rn', 'rn', t('Kirundi', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'kv', 'kv', t('Komi', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'kw', 'kw', t('Cornish', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'la', 'la', t('Latina', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'sm', 'sm', t('Samoan', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'sg', 'sg', t('Sango', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'tn', 'tn', t('Setswana', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'to', 'to', t('Tonga', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'fil', 'fil', t('Filipino', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'ce', 'ce', t('Chechen', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'cv', 'cv', t('Chuvash', [], ['context' => 'translation_connectors']));
    $this->add_relation( 'sn', 'sn', t('Shona', [], ['context' => 'translation_connectors']));
	}

	public function __construct() {
		$this->init();
	}

	/**
	 * @param $drupal_code
	 *
	 * @return LanguageEntity
	 * @throws LanguageNotFoundException
	 */
	public function get_sc_code_by_drupal($drupal_code ) {
		if ( ! isset( $this->drupal_to_sc_relations[ $drupal_code ] ) ) {
			throw new LanguageNotFoundException( t('Not found sc lang by drupal_code = ' . $drupal_code
				, [], ['context' => 'translation_connectors'] ) );
		}

		return $this->drupal_to_sc_relations[ $drupal_code ];
	}

	/**
	 * @param $sc_code
	 *
	 * @return LanguageEntity
	 * @throws LanguageNotFoundException
	 */
	public function get_drupal_code_by_sc($sc_code ) {
		if ( ! isset( $this->sc_to_drupal_relations[ $sc_code ] ) || ! isset( $this->sc_to_drupal_relations[ $sc_code ][0] ) ) {
			throw new LanguageNotFoundException( t('Not found drupal lang by sc_code = ' . $sc_code
				, [], ['context' => 'translation_connectors'] ) );
		}

		return $this->sc_to_drupal_relations[ $sc_code ][0]; //возвращаем первый попавшийся до новых требований
	}

	public function get_all_drupal_codes_by_sc($sc_code ) {
		if ( ! isset( $this->sc_to_drupal_relations[ $sc_code ] ) || ! is_array( $this->sc_to_drupal_relations[ $sc_code ] ) ) {
			throw new LanguageNotFoundException( t('Not found drupal lang by sc_code = ' . $sc_code
				, [], ['context' => 'translation_connectors'] ) );
		}

		return $this->sc_to_drupal_relations[ $sc_code ]; //возвращаем весь массив
	}

	public function get_all_sc_languages() {
		return $this->sc_to_drupal_relations;
	}

	public function get_all_drupal_languages() {
		return $this->drupal_to_sc_relations;
	}

	public function get_sc_codes() {
		return array_keys( $this->sc_to_drupal_relations );
	}

	public function get_drupal_codes() {
		return array_keys( $this->drupal_to_sc_relations );
	}

	public function get_drupal_languages() {
		$result = array_map( function (
			/** @var LanguageEntity $value */
			$value
		) {
			return $value->get_drupal_name();
		}, $this->drupal_to_sc_relations );

		asort( $result );

		return $result;
	}

	public function get_sc_languages() {
		$result = array_map( function (
			/** @var LanguageEntity[] $value */
			$value
		) {
			return $value[0]->get_sc_name();
		}, $this->sc_to_drupal_relations );

		asort( $result );

		return $result;
	}

	public function get_drupal_target_languages($source_language_code ) {
		$languages = $this->get_drupal_languages();
		if ( isset( $languages[ $source_language_code ] ) ) {
			unset( $languages[ $source_language_code ] );
		}

		return $languages;
	}


	public function get_sc_target_languages( $source_language_code ) {
		$languages = $this->get_sc_languages();
		if ( isset( $languages[ $source_language_code ] ) ) {
			unset( $languages[ $source_language_code ] );
		}

		return $languages;
	}

	public function get_polylang_names_to_locales() {
		/** @noinspection PhpUndefinedFunctionInspection */
		$pll_names = pll_languages_list( [ 'fields' => 'name' ] );
		/** @noinspection PhpUndefinedFunctionInspection */
		$pll_locales = pll_languages_list( [ 'fields' => 'locale' ] );

		$result = array_combine( $pll_locales, $pll_names );

		return $result;
	}

	public function get_polylang_names_to_slugs() {
		/** @noinspection PhpUndefinedFunctionInspection */
		$pll_names = pll_languages_list( [ 'fields' => 'name' ] );
		/** @noinspection PhpUndefinedFunctionInspection */
		$pll_slugs = pll_languages_list( [ 'fields' => 'slug' ] );

		$result = array_combine( $pll_names, $pll_slugs );

		return $result;
	}

	public function get_polylang_slugs_to_names() {
		/** @noinspection PhpUndefinedFunctionInspection */
		$pll_slugs = pll_languages_list( [ 'fields' => 'slug' ] );
		/** @noinspection PhpUndefinedFunctionInspection */
		$pll_names = pll_languages_list( [ 'fields' => 'name' ] );

		$result = array_combine( $pll_slugs, $pll_names );

		return $result;
	}

	public function get_polylang_language_name_by_slug($slug)
	{
		$languages = $this->get_polylang_slugs_to_names();
		return $languages[$slug] ?? '';
	}

	//не классичный подход с возвращаемыми параметрами в интерфейсе метода, здесь мне показалось уместным и удобным
	public function get_polylang_languages_supported_by_sc( &$unsupported_languages_array = [] ) {
		$languages = $this->get_polylang_names_to_locales();

		//TODO: возможно, следует пересмотреть интерфейс и вместо эксепшена возвращать false (будет быстрее)
		$result = [];
		foreach ( $languages as $locale => $name ) {
			try {
				$this->get_sc_code_by_drupal( $locale );
			} catch ( LanguageNotFoundException $e ) {
				array_push( $unsupported_languages_array, $name );
				continue;
			}

			$result[ $locale ] = $name;
		}

		return $result;
	}

	//пришлось писать отдельную функцию для фронта
	public function get_polylang_slugs_supported_by_sc() {
		//TODO: далеко не самое оптимальное решение, мб назреет что-то более адекватное
		$name_to_slug    = $this->get_polylang_names_to_slugs();
		$name_to_locales = array_flip( $this->get_polylang_languages_supported_by_sc() );

		$result = [];
		foreach ( $name_to_locales as $name => $locale ) {
			$result[] = $name_to_slug[ $name ];
		}

		return $result;
	}

	public function get_polylang_slugs_to_locales() {
		/** @noinspection PhpUndefinedFunctionInspection */
		$pll_slug = pll_languages_list( [ 'fields' => 'slug' ] );
		/** @noinspection PhpUndefinedFunctionInspection */
		$pll_locales = pll_languages_list( [ 'fields' => 'locale' ] );

		$result = array_combine( $pll_locales, $pll_slug );

		return $result;
	}
}