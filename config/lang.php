<?php

defined('SYSPATH') or die('No direct access allowed.');

return array(
	'default' => 'en',
	'cookie' => 'lang',
	/**
	 * List of all supported languages. Array keys match language segment from the URI.
	 * A default fallback language can be set by Lang::$default.
	 *
	 * Options for each language:
	 *  i18n_code - The target language for the I18n class
	 *  locale    - Locale name(s) for setting all locale information (http://php.net/setlocale)
	 */
	'langs' => array(
		'en' => array(
			'name' => 'English',
			'i18n_code' => 'en',
			'locale' => array('en_US.utf-8'),
		),
	),
);
