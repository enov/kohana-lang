<?php

defined('SYSPATH') or die('No direct script access.');

class Kohana_Lang
{

	/**
	 * Variable to hold the Singleton instance
	 *
	 * @var Lang
	 */
	protected static $_instance = NULL;
	/**
	 * Variable to hold config
	 *
	 * @var Config_Group
	 */
	protected $_config;
	/**
	 * Variable to hold the processed lang
	 *
	 * @var string
	 */
	protected $_lang;
	/**
	 * Variable to hold the default lang
	 *
	 * @var string
	 */
	protected $_default;
	/**
	 * Variable to hold the array of available langs
	 *
	 * @var array
	 */
	protected $_languages;
	/**
	 * Variable to hold the key to use in Cookie
	 *
	 * @var string
	 */
	protected $_cookie;

	/**
	 * Get class instance, Singleton pattern
	 *
	 * @return Lang
	 */
	public static function instance() {
		// Create class instance
		if (static::$_instance === NULL) {
			$class = get_called_class();
			static::$_instance = new $class;
		}
		// Return instance
		return static::$_instance;
	}

	/**
	 * Constructor. Load configuration. protected for Singleton pattern
	 *
	 * @return void
	 * @uses Kohana
	 * @uses Config
	 * @uses Config_Group
	 */
	protected function __construct() {

		// load the config
		$this->_config = Kohana::$config->load('lang');

		// validate the config
		if (!$this->_config->offsetExists('langs'))
			throw new Kohana_Exception('You should configure list of available languages');
		if (!$this->_config->offsetExists('default'))
			throw new Kohana_Exception('You should configure a default language');
		if (!$this->_config->offsetExists('cookie'))
			throw new Kohana_Exception('You should configure a key to use in Cookie');

		// set the properties of this instance
		$this->_default = $this->_config->get('default');
		$this->_languages = (array) $this->_config->get('langs');
		$this->_cookie = $this->_config->get('cookie');
	}

	/**
	 * process Request uri/header and find the language
	 *
	 * 1. first look at the URI
	 * 2. then see if we have anything in Cookie
	 * 3. then look at Accept-Language to find best match
	 * 4. Go for default
	 *
	 * @param Request $request
	 * @return string
	 */
	public function process(Request $request) {

		// hold the uri in temporary var
		$uri = $request->uri();

		// pattern for preg_match
		$pattern = '~^(?:' . implode('|', array_keys($this->_languages)) . ')(?=/|$)~i';

		// 1. Look for a supported language in the first URI segment
		if (preg_match($pattern, $uri, $matches)) {
			// Language found in the URI
			$lang = strtolower($matches[0]);
			/**
			 * set the request uri after removing the language part
			 */
			$request->uri(
			  ltrim
				(
				// remove the lang part at the beginning
				(string) substr($uri, strlen($lang)),
				// then remove `/` at the beginning, if any
				'/'
			  )
			);
		}
		// 2. Look for a language stored in the Cookie
		else if (isset($this->_languages[strtolower(Cookie::get($this->_cookie))])) {
			// Language found in Cookie
			$lang = strtolower(Cookie::get($this->_cookie));
		}
		// 3. Look for a preferred language in the `Accept-Language` header directive.
		else if ($preferred_lang = $request->headers()->preferred_language(array_keys($this->_languages))) {
			$lang = $preferred_lang;
		}
		// 4. no luck, use the default
		else {
			$lang = $this->_default;
		}
		/**
		 * Store language
		 */
		$this->lang($lang);
		// return $lang
		return $lang;
	}

	/**
	 * Get or set the language
	 *
	 * @param string $lang
	 * @return string
	 */
	public function lang($lang = NULL) {

		// param is NULL act as getter
		if ($lang === NULL) {
			// return default_lang() if lang is not set yet
			return $this->_lang ? : $this->default_language();
		}

		// act as setter from this point and onward
		// test if $lang is available
		if (!$this->is_available($lang))
			throw new Kohana_Exception('The specified language is not available');

		// set the lang
		// Store target language in request
		$this->_lang = $lang;
		// Store target language in I18n
		I18n::$lang = $this->_languages[$lang]['i18n_code'];
		// Set locale
		setlocale(LC_ALL, $this->_languages[$lang]['locale']);
		// Update language in cookie
		if (Cookie::get($this->_cookie) !== $lang) {
			Cookie::set($this->_cookie, $lang);
		}

		// return
		return $lang;
	}

	/**
	 * Get list of available languages from config
	 *
	 * @return array
	 * @throws Kohana_Exception
	 */
	public function list_languages() {
		return $this->_languages;
	}

	/**
	 * Get the default language from config
	 *
	 * @param type $default
	 * @return type
	 * @throws Kohana_Exception
	 */
	public function default_language() {
		return $this->_config->get('default');
	}

	/**
	 * Get the language name from $lang code
	 *
	 * @param string $lang
	 * @return string
	 * @throws Kohana_Exception
	 */
	public function language_name($lang = NULL) {
		if ($lang === NULL) {
			$lang = $this->lang();
		} else {
			// test if $lang is available
			if (!is_available($lang))
				throw new Kohana_Exception('The specified language is not available');
		}
		// return
		return $this->_languages[$lang]['name'];
	}

	/**
	 * List language names
	 *
	 * @return array in form of array('en' => 'English', 'hy' => 'Հայերէն')
	 */
	public function list_language_names() {
		return array_map(function($lang) {
			return $lang['name'];
		}, $this->_languages);
	}

	/**
	 * Test if $lang exists in the list of available langs in config
	 *
	 * @uses Lang::langs()
	 * @param type $lang
	 * @return bool returns TRUE if $lang is available, otherwise FALSE
	 */
	public function is_available($lang) {
		return (bool) array_key_exists($lang, $this->_languages);
	}

	/**
	 * Helper function that uses URL::site for setting a language key.
	 * The current language (Lang::$lang) used by default, if $lang not passed
	 *
	 *     // custom language
	 *     echo Lang::instance()->url_site('foo/bar', 'fr');
	 *
	 *     // no language
	 *     echo Lang::instance()->url_site('foo/bar', FALSE);
	 *
	 *     // prints current uri with current language
	 *     echo Lang::instance()->url_site();
	 *
	 *     // create a link to switch language for the current page
	 *     echo Lang::instance()->url_site(FALSE, 'fr');
	 *
	 *
	 * @param mixed $uri
	 * @param mixed $lang language key to prepend to the URI, or FALSE to not to
	 * @return type
	 */
	public function url_site($uri = FALSE, $lang = TRUE, $protocol = NULL, $index = TRUE) {
		// if $uri is not specified get it from Request
		if (empty($uri)) {
			$uri = Request::initial()->uri();
		}
		if ($lang === TRUE) {
			// Prepend the current language to the URI
			$uri = $this->lang() . '/' . ltrim($uri, '/');
		} elseif (is_string($lang)) {
			if (!$this->is_available($lang))
				throw new Kohana_Exception('The specified language is not available');
			// Prepend a custom language to the URI
			$uri = $lang . '/' . ltrim($uri, '/');
		} else {
			$uri = ltrim($uri, '/');
		}

		return URL::site($uri, $protocol, $index);
	}

	/**
	 *
	 * @param type $view
	 * @param type $lang
	 * @return type
	 * @throws Kohana_Exception
	 */
	public function view_factory($view, $lang = FALSE) {
		$lang ? : $this->lang();
		if (!$this->is_available($lang))
			throw new Kohana_Exception('The specified language is not available');
		return View::factory($lang . '/' . $view);
	}

}
