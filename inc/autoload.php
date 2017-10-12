<?php

define('SMARTCAT_PLUGIN_FILE', __FILE__);
define('SMARTCAT_PLUGIN_NAME', 'translation_connectors');

define(
  'SMARTCAT_PLUGIN_DIR',
  realpath( pathinfo( __FILE__, PATHINFO_DIRNAME ) ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
);


$composer_autoload_script_file = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if ( file_exists( $composer_autoload_script_file ) && is_readable( $composer_autoload_script_file ) ) {
	/** @noinspection PhpIncludeInspection */
	require_once $composer_autoload_script_file;
} else {
	throw new \Exception(
		vsprintf(
			"Expected autoloader script not found at '%s'",
			[ $composer_autoload_script_file ]
		), 1 );
}

//require_once __DIR__ .  'AutoloaderClass.php';
